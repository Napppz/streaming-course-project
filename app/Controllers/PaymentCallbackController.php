<?php

namespace App\Controllers;

use App\Models\CoursePaymentTransactionModel;
use App\Models\EnrollmentModel;
use InvalidArgumentException;

class PaymentCallbackController extends BaseController
{
    protected CoursePaymentTransactionModel $paymentTransactionModel;
    protected EnrollmentModel $enrollmentModel;
    protected $xenditPaymentLinkService;
    protected $db;

    public function __construct()
    {
        $this->paymentTransactionModel = new CoursePaymentTransactionModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->xenditPaymentLinkService = service('xenditPaymentLinks');
        $this->db = \Config\Database::connect();
    }

    public function xenditWebhook()
    {
        $callbackToken = $this->request->getHeaderLine('x-callback-token');

        if (!$this->xenditPaymentLinkService->isValidCallbackToken($callbackToken)) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Invalid callback token.',
            ]);
        }

        $payload = $this->request->getJSON(true);

        if (!is_array($payload) || $payload === []) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Webhook payload must be valid JSON.',
            ]);
        }

        $webhookData = $this->extractWebhookData($payload);
        $rawStatus = $this->extractFirstString($webhookData, ['status', 'payment_status', 'invoice_status']);
        $rawEvent = $this->extractFirstString($payload, ['event', 'event_type', 'type'])
            ?? $this->extractFirstString($webhookData, ['event', 'event_type', 'type']);

        try {
            $incomingStatus = $this->xenditPaymentLinkService->mapToInternalStatus($rawStatus, $rawEvent);
        } catch (InvalidArgumentException $exception) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }

        $transaction = $this->paymentTransactionModel->findByWebhookPayload($webhookData);

        if (!$transaction) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Transaction not found for webhook payload.',
            ]);
        }

        $this->db->transBegin();

        try {
            $lockedTransaction = $this->lockTransactionForSettlement((int) $transaction['id']);

            if (!$lockedTransaction) {
                throw new \RuntimeException('Transaction disappeared before settlement.');
            }

            $persistedStatus = $this->resolvePersistedStatus((string) ($lockedTransaction['status'] ?? 'pending'), $incomingStatus);
            $updateData = $this->buildWebhookUpdateData($lockedTransaction, $webhookData, $persistedStatus, $rawStatus);

            if ($persistedStatus === 'paid') {
                $updateData = array_merge($updateData, $this->buildEnrollmentGrantData($lockedTransaction));
            }

            $this->paymentTransactionModel->update((int) $lockedTransaction['id'], $updateData);

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to persist webhook settlement.');
            }

            $this->db->transCommit();

            return $this->response->setJSON([
                'success' => true,
                'transaction_id' => (int) $lockedTransaction['id'],
                'status' => $persistedStatus,
            ]);
        } catch (\Throwable $exception) {
            $this->db->transRollback();

            log_message('error', 'Xendit webhook settlement failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Failed to settle webhook.',
            ]);
        }
    }

    public function xenditReturn()
    {
        return $this->renderReturnStatusPage('return');
    }

    public function xenditFailureReturn()
    {
        return $this->renderReturnStatusPage('failure');
    }

    public function xenditSuccessRedirect()
    {
        return $this->redirectAfterBrowserReturn('return');
    }

    public function xenditFailureRedirect()
    {
        return $this->redirectAfterBrowserReturn('failure');
    }

    private function renderReturnStatusPage(string $returnContext)
    {
        $userId = (int) $this->getCurrentUserId();
        $transaction = $this->paymentTransactionModel->findReturnTransactionForUser($userId, $this->request->getGet());
        $transaction = $this->syncReturnTransactionFromProvider($transaction);
        $statusMeta = $this->resolveStatusMeta($transaction['status'] ?? null, $returnContext);

        return view('user/payment_return_status', [
            'transaction' => $transaction,
            'statusMeta' => $statusMeta,
        ]);
    }

    private function redirectAfterBrowserReturn(string $returnContext)
    {
        $userId = (int) $this->getCurrentUserId();
        $transaction = $this->paymentTransactionModel->findReturnTransactionForUser($userId, $this->request->getGet());
        $transaction = $this->syncReturnTransactionFromProvider($transaction);
        $statusMeta = $this->resolveStatusMeta($transaction['status'] ?? null, $returnContext);

        $flashKey = match ($statusMeta['variant']) {
            'success' => 'success',
            'error' => 'error',
            default => 'message',
        };

        if ($returnContext === 'failure' && !empty($transaction['course_id'])) {
            return redirect()
                ->to(site_url('user/view-course/' . $transaction['course_id']))
                ->with($flashKey, $statusMeta['message']);
        }

        return redirect()
            ->to(site_url('user/payment-history'))
            ->with($flashKey, $statusMeta['message']);
    }

    private function syncReturnTransactionFromProvider(?array $transaction): ?array
    {
        if (!$transaction || !$this->canSyncTransactionFromProvider($transaction)) {
            return $transaction;
        }

        try {
            $providerResponse = $this->xenditPaymentLinkService->getInvoiceById((string) $transaction['xendit_invoice_id']);
            $providerMetadata = $providerResponse['provider_metadata'] ?? [];
            $incomingStatus = (string) ($providerMetadata['status'] ?? 'pending');
        } catch (\Throwable $exception) {
            log_message('warning', 'Xendit return status sync failed for transaction {transactionId}: {message}', [
                'transactionId' => $transaction['id'] ?? 'unknown',
                'message' => $exception->getMessage(),
            ]);

            return $transaction;
        }

        $this->db->transBegin();

        try {
            $lockedTransaction = $this->lockTransactionForSettlement((int) $transaction['id']);

            if (!$lockedTransaction) {
                throw new \RuntimeException('Transaction disappeared before provider status sync.');
            }

            $persistedStatus = $this->resolvePersistedStatus((string) ($lockedTransaction['status'] ?? 'pending'), $incomingStatus);
            $updateData = $this->buildProviderSyncUpdateData($lockedTransaction, $providerMetadata, $persistedStatus);

            if ($persistedStatus === 'paid') {
                $updateData = array_merge($updateData, $this->buildEnrollmentGrantData($lockedTransaction));
            }

            $this->paymentTransactionModel->update((int) $lockedTransaction['id'], $updateData);

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to persist provider status sync.');
            }

            $this->db->transCommit();

            return $this->paymentTransactionModel->findReturnTransactionForUser(
                (int) $lockedTransaction['user_id'],
                ['id' => $lockedTransaction['xendit_invoice_id'] ?? $transaction['xendit_invoice_id']]
            ) ?: array_merge($lockedTransaction, $updateData);
        } catch (\Throwable $exception) {
            $this->db->transRollback();

            log_message('error', 'Xendit return settlement failed: {message}', [
                'message' => $exception->getMessage(),
            ]);

            return $transaction;
        }
    }

    private function canSyncTransactionFromProvider(array $transaction): bool
    {
        if (($transaction['provider'] ?? null) !== 'xendit') {
            return false;
        }

        if (empty($transaction['xendit_invoice_id'])) {
            return false;
        }

        return ($transaction['status'] ?? 'pending') === 'pending';
    }

    private function lockTransactionForSettlement(int $transactionId): ?array
    {
        $query = $this->db->query(
            'SELECT * FROM course_payment_transactions WHERE id = ? FOR UPDATE',
            [$transactionId]
        );

        $transaction = $query->getRowArray();

        return $transaction ?: null;
    }

    private function buildWebhookUpdateData(array $transaction, array $payload, string $persistedStatus, ?string $rawStatus): array
    {
        $now = date('Y-m-d H:i:s');
        $updateData = [
            'status' => $persistedStatus,
            'xendit_status' => $rawStatus !== null && trim($rawStatus) !== '' ? trim($rawStatus) : ($transaction['xendit_status'] ?? null),
            'xendit_invoice_id' => $this->extractFirstString($payload, ['id', 'invoice_id', 'payment_id']) ?? ($transaction['xendit_invoice_id'] ?? null),
            'xendit_external_id' => $this->extractFirstString($payload, ['external_id', 'reference_code']) ?? ($transaction['xendit_external_id'] ?? null),
            'last_webhook_at' => $now,
            'failure_code' => $this->extractFirstString($payload, ['failure_code', 'error_code']),
            'failure_message' => $this->extractFirstString($payload, ['failure_message', 'error_message', 'message']),
        ];

        if ($persistedStatus === 'paid') {
            $updateData['paid_at'] = $transaction['paid_at'] ?: $this->extractTimestamp($payload, [
                'paid_at',
                'payment_timestamp',
                'updated',
                'updated_at',
            ]) ?: $now;
            $updateData['expired_at'] = $transaction['expired_at'] ?? null;
            $updateData['cancelled_at'] = $transaction['cancelled_at'] ?? null;
            $updateData['failure_code'] = null;
            $updateData['failure_message'] = null;

            return $updateData;
        }

        if ($persistedStatus === 'expired' && empty($transaction['expired_at'])) {
            $updateData['expired_at'] = $this->extractTimestamp($payload, ['expired_at', 'expiry_date', 'updated', 'updated_at']) ?: $now;
        }

        if ($persistedStatus === 'cancelled' && empty($transaction['cancelled_at'])) {
            $updateData['cancelled_at'] = $this->extractTimestamp($payload, ['cancelled_at', 'updated', 'updated_at']) ?: $now;
        }

        if ($persistedStatus === 'pending') {
            $updateData['failure_code'] = null;
            $updateData['failure_message'] = null;
        }

        return $updateData;
    }

    private function buildProviderSyncUpdateData(array $transaction, array $providerMetadata, string $persistedStatus): array
    {
        $now = date('Y-m-d H:i:s');
        $updateData = [
            'status' => $persistedStatus,
            'xendit_status' => $providerMetadata['xendit_status'] ?? ($transaction['xendit_status'] ?? null),
            'xendit_invoice_id' => $providerMetadata['xendit_invoice_id'] ?? ($transaction['xendit_invoice_id'] ?? null),
            'xendit_external_id' => $providerMetadata['xendit_external_id'] ?? ($transaction['xendit_external_id'] ?? null),
            'xendit_invoice_url' => $providerMetadata['xendit_invoice_url'] ?? ($transaction['xendit_invoice_url'] ?? null),
            'checkout_url' => $providerMetadata['checkout_url'] ?? ($transaction['checkout_url'] ?? null),
            'success_redirect_url' => $providerMetadata['success_redirect_url'] ?? ($transaction['success_redirect_url'] ?? null),
            'failure_redirect_url' => $providerMetadata['failure_redirect_url'] ?? ($transaction['failure_redirect_url'] ?? null),
            'expires_at' => $providerMetadata['expires_at'] ?? ($transaction['expires_at'] ?? null),
        ];

        if ($persistedStatus === 'paid') {
            $updateData['paid_at'] = $transaction['paid_at'] ?: ($providerMetadata['paid_at'] ?? null) ?: $now;
            $updateData['expired_at'] = $transaction['expired_at'] ?? null;
            $updateData['cancelled_at'] = $transaction['cancelled_at'] ?? null;
            $updateData['failure_code'] = null;
            $updateData['failure_message'] = null;
        }

        if ($persistedStatus === 'expired' && empty($transaction['expired_at'])) {
            $updateData['expired_at'] = $providerMetadata['expires_at'] ?? $now;
        }

        if ($persistedStatus === 'cancelled' && empty($transaction['cancelled_at'])) {
            $updateData['cancelled_at'] = $now;
        }

        return $updateData;
    }

    private function buildEnrollmentGrantData(array $transaction): array
    {
        $now = date('Y-m-d H:i:s');
        $enrollment = $this->enrollmentModel->getEnrollment((int) $transaction['user_id'], (int) $transaction['course_id']);

        if (!$enrollment) {
            try {
                $this->enrollmentModel->enrollUser((int) $transaction['user_id'], (int) $transaction['course_id']);
            } catch (\Throwable $exception) {
                log_message('warning', 'Enrollment grant retry after webhook paid path: {message}', [
                    'message' => $exception->getMessage(),
                ]);
            }

            $enrollment = $this->enrollmentModel->getEnrollment((int) $transaction['user_id'], (int) $transaction['course_id']);
        }

        if (!$enrollment) {
            throw new \RuntimeException('Paid transaction could not be linked to an enrollment.');
        }

        return [
            'granted_enrollment_id' => (int) $enrollment['id'],
            'granted_at' => $transaction['granted_at'] ?: $now,
        ];
    }

    private function resolvePersistedStatus(string $currentStatus, string $incomingStatus): string
    {
        if ($currentStatus === 'paid' && $incomingStatus !== 'paid') {
            return 'paid';
        }

        return $incomingStatus;
    }

    private function extractWebhookData(array $payload): array
    {
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        return $payload;
    }

    private function extractFirstString(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($payload[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function extractTimestamp(array $payload, array $keys): ?string
    {
        $value = $this->extractFirstString($payload, $keys);

        if ($value === null) {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return null;
        }
    }

    private function resolveStatusMeta(?string $status, string $returnContext): array
    {
        if ($status === 'paid') {
            return [
                'variant' => 'success',
                'badge' => 'Pembayaran berhasil',
                'title' => 'Pembayaran kamu berhasil',
                'message' => 'Akses kursus sudah aktif. Kamu sekarang bisa langsung mulai belajar.',
            ];
        }

        if ($status === 'failed' || $status === 'expired' || $status === 'cancelled') {
            $messages = [
                'failed' => 'Pembayaran tidak berhasil. Kamu bisa mencoba checkout lagi dari halaman kursus.',
                'expired' => 'Sesi pembayaran sudah kedaluwarsa. Buat checkout baru dari halaman kursus jika masih ingin membeli.',
                'cancelled' => 'Pembayaran dibatalkan. Buat checkout baru dari halaman kursus jika masih ingin membeli.',
            ];

            return [
                'variant' => 'error',
                'badge' => 'Pembayaran tidak selesai',
                'title' => 'Status pembayaran terakhir: ' . strtoupper($status),
                'message' => $messages[$status],
            ];
        }

        return [
            'variant' => 'info',
            'badge' => $returnContext === 'failure' ? 'Menunggu sinkronisasi status' : 'Pembayaran sedang diproses',
            'title' => 'Pembayaran kamu sedang diproses',
            'message' => 'Kami masih memeriksa status pembayaranmu. Jika akses kursus belum muncul sekarang, coba cek lagi beberapa saat lagi.',
        ];
    }
}
