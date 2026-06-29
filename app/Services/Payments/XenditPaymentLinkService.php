<?php

namespace App\Services\Payments;

use CodeIgniter\HTTP\CURLRequest;
use Config\Xendit;
use InvalidArgumentException;
use RuntimeException;

class XenditPaymentLinkService
{
    public function __construct(
        private CURLRequest $httpClient,
        private Xendit $config,
        private XenditPaymentStatusMapper $statusMapper
    ) {
    }

    /**
     * @param array<string, mixed> $transactionData
     *
     * @return array<string, mixed>
     */
    public function buildPaymentLinkPayload(array $transactionData): array
    {
        $courseId = $this->requireInt($transactionData, 'course_id');
        $userId = $this->requireInt($transactionData, 'user_id');
        $amount = $this->requirePositiveAmount($transactionData, 'amount');
        $courseTitle = $this->requireString($transactionData, 'course_title');
        $email = $this->requireString($transactionData, 'customer_email');
        $customerName = $this->requireString($transactionData, 'customer_name');

        $historicalAttemptCount = max(0, (int) ($transactionData['historical_attempt_count'] ?? 0));
        $referenceCode = $transactionData['reference_code'] ?? $this->buildReferenceCode($courseId, $userId, $historicalAttemptCount);
        $description = trim((string) ($transactionData['description'] ?? ('Pembayaran course premium: ' . $courseTitle)));
        $currency = strtoupper((string) ($transactionData['currency'] ?? $this->config->currency));
        $invoiceDuration = max(1, (int) ($transactionData['invoice_duration'] ?? $this->config->invoiceDuration));
        $successRedirectUrl = trim((string) ($transactionData['success_redirect_url'] ?? $this->currentSiteUrl('payment/xendit/success')));
        $failureRedirectUrl = trim((string) ($transactionData['failure_redirect_url'] ?? $this->currentSiteUrl('payment/xendit/failure')));

        if ($successRedirectUrl === '') {
            $successRedirectUrl = trim($this->config->successRedirectUrl);
        }

        if ($failureRedirectUrl === '') {
            $failureRedirectUrl = trim($this->config->failureRedirectUrl);
        }

        if ($successRedirectUrl === '' || $failureRedirectUrl === '') {
            throw new InvalidArgumentException('Xendit redirect URLs must be configured before building a payment payload.');
        }

        [$givenNames, $surname] = $this->splitCustomerName($customerName);

        $item = [
            'name' => $courseTitle,
            'quantity' => 1,
            'price' => $amount,
            'category' => 'course',
        ];

        $courseUrl = trim((string) ($transactionData['course_url'] ?? ''));
        if ($courseUrl !== '') {
            $item['url'] = $courseUrl;
        }

        $metadata = [
            'provider' => 'xendit',
            'course_id' => $courseId,
            'user_id' => $userId,
            'reference_code' => $referenceCode,
            'historical_attempt_count' => $historicalAttemptCount,
        ];

        if (isset($transactionData['metadata']) && is_array($transactionData['metadata'])) {
            $metadata = array_merge($metadata, $transactionData['metadata']);
        }

        $customer = [
            'given_names' => $givenNames,
            'email' => $email,
        ];

        if ($surname !== '') {
            $customer['surname'] = $surname;
        }

        $payload = [
            'external_id' => $referenceCode,
            'amount' => $amount,
            'description' => $description,
            'invoice_duration' => $invoiceDuration,
            'currency' => $currency,
            'success_redirect_url' => $successRedirectUrl,
            'failure_redirect_url' => $failureRedirectUrl,
            'customer' => $customer,
            'items' => [$item],
            'metadata' => $metadata,
        ];

        $mobileNumber = trim((string) ($transactionData['customer_phone'] ?? ''));
        if ($mobileNumber !== '') {
            $payload['customer']['mobile_number'] = $mobileNumber;
        }

        return $payload;
    }

    public function buildReferenceCode(int $courseId, int $userId, int $historicalAttemptCount = 0): string
    {
        $referenceCode = sprintf('COURSE-%d-USER-%d', $courseId, $userId);

        if ($historicalAttemptCount <= 0) {
            return $referenceCode;
        }

        return sprintf('%s-R%d', $referenceCode, $historicalAttemptCount + 1);
    }

    /**
     * @param array<string, mixed> $transactionData
     *
     * @return array{request_payload: array<string, mixed>, response_payload: array<string, mixed>, provider_metadata: array<string, mixed>}
     */
    public function createPaymentLink(array $transactionData): array
    {
        $this->assertApiReady();

        $payload = $this->buildPaymentLinkPayload($transactionData);
        $response = $this->httpClient->post('v2/invoices', [
            'json' => $payload,
        ]);
        $decodedResponse = $this->decodeJsonResponse($response, 'create Xendit invoice');

        return [
            'request_payload' => $payload,
            'response_payload' => $decodedResponse,
            'provider_metadata' => $this->extractProviderMetadata($decodedResponse),
        ];
    }

    /**
     * @return array{response_payload: array<string, mixed>, provider_metadata: array<string, mixed>}
     */
    public function getInvoiceById(string $invoiceId): array
    {
        $this->assertApiReady();

        $invoiceId = trim($invoiceId);

        if ($invoiceId === '') {
            throw new InvalidArgumentException('Missing Xendit invoice ID.');
        }

        $response = $this->httpClient->get('v2/invoices/' . rawurlencode($invoiceId));
        $decodedResponse = $this->decodeJsonResponse($response, 'get Xendit invoice');

        return [
            'response_payload' => $decodedResponse,
            'provider_metadata' => $this->extractProviderMetadata($decodedResponse),
        ];
    }

    /**
     * @param array<string, mixed> $responsePayload
     *
     * @return array<string, mixed>
     */
    public function extractProviderMetadata(array $responsePayload): array
    {
        $xenditStatus = isset($responsePayload['status']) ? (string) $responsePayload['status'] : null;
        $externalId = isset($responsePayload['external_id']) ? (string) $responsePayload['external_id'] : null;
        $invoiceId = isset($responsePayload['id']) ? (string) $responsePayload['id'] : null;
        $checkoutUrl = isset($responsePayload['invoice_url']) ? (string) $responsePayload['invoice_url'] : null;
        $expiryDate = isset($responsePayload['expiry_date']) ? (string) $responsePayload['expiry_date'] : null;
        $paidAt = isset($responsePayload['paid_at']) ? (string) $responsePayload['paid_at'] : null;
        $updatedAt = isset($responsePayload['updated']) ? (string) $responsePayload['updated'] : null;
        $normalizedStatus = $this->statusMapper->normalize($xenditStatus);

        if ($paidAt === null && $normalizedStatus === 'paid') {
            $paidAt = $updatedAt;
        }

        return [
            'provider' => 'xendit',
            'reference_code' => $externalId,
            'external_id' => $externalId,
            'provider_reference_id' => $invoiceId,
            'checkout_url' => $checkoutUrl,
            'xendit_invoice_id' => $invoiceId,
            'xendit_external_id' => $externalId,
            'xendit_invoice_url' => $checkoutUrl,
            'xendit_status' => $xenditStatus,
            'status' => $normalizedStatus,
            'expires_at' => $this->normalizeDateTime($expiryDate),
            'paid_at' => $this->normalizeDateTime($paidAt),
            'success_redirect_url' => isset($responsePayload['success_redirect_url'])
                ? (string) $responsePayload['success_redirect_url']
                : $this->config->successRedirectUrl,
            'failure_redirect_url' => isset($responsePayload['failure_redirect_url'])
                ? (string) $responsePayload['failure_redirect_url']
                : $this->config->failureRedirectUrl,
        ];
    }

    public function mapToInternalStatus(?string $status, ?string $event = null): string
    {
        return $this->statusMapper->normalize($status, $event);
    }

    public function isValidCallbackToken(?string $providedToken): bool
    {
        $configuredToken = trim($this->config->callbackToken);
        $providedToken = trim((string) $providedToken);

        if ($configuredToken === '' || $providedToken === '') {
            return false;
        }

        return hash_equals($configuredToken, $providedToken);
    }

    public function getStatusMapper(): XenditPaymentStatusMapper
    {
        return $this->statusMapper;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonResponse($response, string $action): array
    {
        $body = (string) $response->getBody();
        $decodedResponse = json_decode($body, true);

        if (!is_array($decodedResponse)) {
            throw new RuntimeException('Unexpected Xendit response while trying to ' . $action . ': unable to decode JSON body.');
        }

        $statusCode = (int) $response->getStatusCode();

        if ($statusCode >= 400) {
            $message = $this->extractXenditErrorMessage($decodedResponse);

            throw new RuntimeException(sprintf(
                'Xendit rejected request while trying to %s. HTTP %d: %s',
                $action,
                $statusCode,
                $message
            ));
        }

        return $decodedResponse;
    }

    /**
     * @param array<string, mixed> $responsePayload
     */
    private function extractXenditErrorMessage(array $responsePayload): string
    {
        foreach (['message', 'error_message', 'detail'] as $key) {
            $value = trim((string) ($responsePayload[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        if (isset($responsePayload['errors']) && is_array($responsePayload['errors'])) {
            return json_encode($responsePayload['errors']) ?: 'Unknown Xendit validation error.';
        }

        return 'Unknown Xendit validation error.';
    }

    private function assertApiReady(): void
    {
        if (trim($this->config->secretKey) === '') {
            throw new RuntimeException('Xendit secret key is not configured.');
        }
    }

    private function currentSiteUrl(string $path): string
    {
        if (!function_exists('site_url') && function_exists('helper')) {
            helper('url');
        }

        return function_exists('site_url') ? site_url($path) : '';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireInt(array $data, string $key): int
    {
        if (!isset($data[$key]) || !is_numeric($data[$key])) {
            throw new InvalidArgumentException(sprintf('Missing numeric field: %s', $key));
        }

        return (int) $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requirePositiveAmount(array $data, string $key): int
    {
        $amount = $this->requireInt($data, $key);

        if ($amount <= 0) {
            throw new InvalidArgumentException(sprintf('Field %s must be greater than zero.', $key));
        }

        return $amount;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireString(array $data, string $key): string
    {
        $value = trim((string) ($data[$key] ?? ''));

        if ($value === '') {
            throw new InvalidArgumentException(sprintf('Missing required string field: %s', $key));
        }

        return $value;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitCustomerName(string $customerName): array
    {
        $parts = preg_split('/\s+/', trim($customerName)) ?: [];
        $givenNames = array_shift($parts) ?: $customerName;
        $surname = implode(' ', $parts);

        return [$givenNames, $surname];
    }

    private function normalizeDateTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return null;
        }
    }
}
