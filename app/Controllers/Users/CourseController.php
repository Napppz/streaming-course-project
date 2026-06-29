<?php

namespace App\Controllers\Users;

use App\Controllers\BaseController;
use App\Models\CourseModel;
use App\Models\CoursePaymentTransactionModel;
use App\Models\CategoryModel;
use App\Models\CourseReviewModel;
use App\Models\EnrollmentModel;
use App\Models\ModuleModel;
use App\Models\LessonModel;
use RuntimeException;

class CourseController extends BaseController
{
    protected $currentUser;
    protected $courseModel;
    protected $moduleModel;
    protected $lessonModel;
    protected $enrollmentModel;
    protected $categoryModel;
    protected $paymentTransactionModel;
    protected $xenditPaymentLinkService;
    
    public function __construct()
    {
        // Set currentUser jika user sudah login
        if (session()->has('id')) {
            $userModel = new \App\Models\UserModel();
            $this->currentUser = $userModel->find(session()->get('id'));
        }
        
        $this->courseModel = new CourseModel();
        $this->moduleModel = new ModuleModel();
        $this->lessonModel = new LessonModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->categoryModel = new CategoryModel();
        $this->paymentTransactionModel = new CoursePaymentTransactionModel();
        $this->xenditPaymentLinkService = service('xenditPaymentLinks');
    }
    
    protected function isLoggedIn()
    {
        return session()->has('id');
    }
    
    public function index()
    {
        $userId = $this->currentUser['id'];
        
        // Get filter parameters from URL
        $category = $this->request->getGet('category');
        $level = $this->request->getGet('level');
        
        // Prepare filters
        $filters = [];
        if (!empty($category)) {
            $filters['category'] = $category;
        }
        if (!empty($level)) {
            $filters['level'] = $level;
        }
        
        // Get courses with filters
        $courses = $this->courseModel->getFilteredCourses($filters);
        
        // Check which courses the user is enrolled in and get progress
        $enrolledCourseIds = [];
        $courseProgress = [];
        $preparedCourses = [];
        
        foreach ($courses as $course) {
            if ($this->enrollmentModel->isEnrolled($userId, $course['id'])) {
                $enrolledCourseIds[] = $course['id'];
                
                // Get progress for this course
                $enrollment = $this->enrollmentModel->where('user_id', $userId)
                    ->where('course_id', $course['id'])
                    ->first();
                    
                $courseProgress[$course['id']] = isset($enrollment['progress_percentage']) ? 
                    $enrollment['progress_percentage'] : 0;
            }

            $course['purchase_state'] = $this->buildPurchaseState($course, in_array($course['id'], $enrolledCourseIds, true));
            $course['checkout_url'] = site_url('user/view-course/' . $course['id'] . '/checkout');
            $preparedCourses[] = $course;
        }

        $courses = $preparedCourses;
        
        // Get categories for filtering
        $categories = $this->categoryModel->findAll();
        
        // Get featured courses
        $featuredCourses = $this->courseModel->getFeaturedCourses();
        
        return view('user/courses', [
            'user' => $this->currentUser,
            'courses' => $courses,
            'enrolledCourseIds' => $enrolledCourseIds,
            'courseProgress' => $courseProgress,
            'categories' => $categories,
            'featuredCourses' => $featuredCourses,
            'selectedCategory' => $category,
            'selectedLevel' => $level
        ]);
    }
    
    public function enrolled()
    {
        $userId = $this->currentUser['id'];
        
        // Get user's enrolled courses
        $enrolledCourses = $this->enrollmentModel->getUserEnrollments($userId);
        
        // Group courses by progress status
        $inProgress = [];
        $completed = [];
        $notStarted = [];
        
        foreach ($enrolledCourses as $course) {
            if ($course['progress_percentage'] == 100) {
                $completed[] = $course;
            } else if ($course['progress_percentage'] > 0) {
                $inProgress[] = $course;
            } else {
                $notStarted[] = $course;
            }
        }
        
        return view('user/enrolled-courses', [
            'user' => $this->currentUser,
            'inProgress' => $inProgress,
            'completed' => $completed,
            'notStarted' => $notStarted,
            'totalEnrolled' => count($enrolledCourses)
        ]);
    }
    
    public function viewCourse($id)
    {
        // Mendapatkan detail course
        $course = $this->courseModel->find($id);
        
        if (!$course) {
            return redirect()->back()->with('error', 'Kursus tidak ditemukan.');
        }

        // Mendapatkan semua modul dari course ini
        $modules = $this->moduleModel->where('course_id', $id)
                          ->orderBy('order_index', 'ASC')
                          ->findAll();
        
        // Mendapatkan semua lesson untuk setiap modul
        foreach ($modules as &$module) {
            $module['lessons'] = $this->lessonModel->where('module_id', $module['id'])
                                      ->orderBy('order_index', 'ASC')
                                      ->findAll();
        }
        
        // Cek jika user sudah login
        $isEnrolled = false;
        $progress = 0;
        
        if ($this->isLoggedIn()) {
            // Cek apakah user sudah terdaftar di kursus ini
            $isEnrolled = $this->enrollmentModel->isEnrolled($this->currentUser['id'], $id);
            
            // Jika sudah terdaftar, ambil progress
            if ($isEnrolled) {
                $progressData = $this->enrollmentModel->getEnrollmentWithProgress($this->currentUser['id'], $id);
                $progress = $progressData ? $progressData['progress_percentage'] : 0;
            }
        }
        
        // Mengambil data rating dari CourseReviewModel
        $courseReviewModel = new CourseReviewModel();
        $averageRating = $courseReviewModel->getAverageRating($id);
        $totalRatings = $courseReviewModel->where('course_id', $id)->countAllResults();
        $purchaseState = $this->buildPurchaseState($course, $isEnrolled);
        $course['purchase_state'] = $purchaseState;
        $course['checkout_url'] = site_url('user/view-course/' . $course['id'] . '/checkout');
        $course['enroll_url'] = site_url('course/' . $course['id'] . '/enroll');
        
        return view('user/view_course', [
            'course' => $course,
            'modules' => $modules,
            'isEnrolled' => $isEnrolled,
            'progress' => $progress,
            'averageRating' => $averageRating,
            'totalRatings' => $totalRatings,
            'purchaseState' => $purchaseState,
        ]);
    }

    public function checkout($id)
    {
        $course = $this->courseModel->find($id);

        if (!$course) {
            return redirect()->to(site_url('user/courses'))->with('error', 'Kursus tidak ditemukan.');
        }

        $userId = (int) $this->currentUser['id'];
        $isEnrolled = $this->enrollmentModel->isEnrolled($userId, $id);

        if ($isEnrolled) {
            return redirect()->to(site_url('course/' . $id))->with('success', 'Kamu sudah memiliki akses ke kursus ini.');
        }

        $purchaseState = $this->buildPurchaseState($course, false);

        if (!$purchaseState['is_premium']) {
            return redirect()->to(site_url('course/' . $id . '/enroll'))->with('success', 'Kursus ini gratis dan bisa langsung didaftarkan.');
        }

        if (!$purchaseState['can_checkout']) {
            return redirect()->to(site_url('user/view-course/' . $id))->with('error', $purchaseState['checkout_blocked_message']);
        }

        $pendingTransaction = $this->paymentTransactionModel->findActivePendingTransaction($userId, (int) $id);

        return view('user/course_checkout', [
            'course' => $course,
            'purchaseState' => $purchaseState,
            'pendingTransaction' => $pendingTransaction,
        ]);
    }

    public function startCheckout($id)
    {
        $course = $this->courseModel->find($id);

        if (!$course) {
            return redirect()->to(site_url('user/courses'))->with('error', 'Kursus tidak ditemukan.');
        }

        $userId = (int) $this->currentUser['id'];

        if ($this->enrollmentModel->isEnrolled($userId, $id)) {
            return redirect()->to(site_url('course/' . $id))->with('success', 'Kamu sudah memiliki akses ke kursus ini.');
        }

        $purchaseState = $this->buildPurchaseState($course, false);

        if (!$purchaseState['is_premium']) {
            return redirect()->to(site_url('course/' . $id . '/enroll'))->with('success', 'Kursus ini gratis dan bisa langsung didaftarkan.');
        }

        if (!$purchaseState['can_checkout']) {
            return redirect()->to(site_url('user/view-course/' . $id))->with('error', $purchaseState['checkout_blocked_message']);
        }

        $transaction = null;

        try {
            $this->paymentTransactionModel->acquireCheckoutLock($userId, (int) $id);
            $this->paymentTransactionModel->expireStalePendingTransactions($userId, (int) $id);

            $transaction = $this->paymentTransactionModel->findActivePendingTransaction($userId, (int) $id);

            if ($transaction && !empty($transaction['checkout_url'])) {
                return redirect()->to($transaction['checkout_url']);
            }

            if (!$transaction) {
                $transaction = $this->createPendingTransaction($course);
            }

            $customerName = trim((string) ($this->currentUser['full_name'] ?? ''));
            if ($customerName === '') {
                $customerName = (string) ($this->currentUser['username'] ?? 'Peserta Kursus');
            }

            $paymentLink = $this->xenditPaymentLinkService->createPaymentLink([
                'course_id' => (int) $course['id'],
                'user_id' => $userId,
                'amount' => (int) ($course['price_amount'] ?? 0),
                'currency' => (string) ($course['price_currency'] ?? 'IDR'),
                'course_title' => (string) $course['title'],
                'course_url' => site_url('user/view-course/' . $course['id']),
                'success_redirect_url' => site_url('payment/xendit/success'),
                'failure_redirect_url' => site_url('payment/xendit/failure'),
                'customer_email' => (string) ($this->currentUser['email'] ?? ''),
                'customer_name' => $customerName,
                'customer_phone' => (string) ($transaction['customer_phone'] ?? ''),
                'reference_code' => (string) $transaction['reference_code'],
                'metadata' => [
                    'course_title' => (string) $course['title'],
                ],
            ]);

            $providerMetadata = $paymentLink['provider_metadata'] ?? [];
            $checkoutUrl = trim((string) ($providerMetadata['checkout_url'] ?? ''));

            if ($checkoutUrl === '') {
                throw new RuntimeException('Checkout URL tidak diterima dari Xendit.');
            }

            $this->paymentTransactionModel->update($transaction['id'], [
                'provider' => (string) ($providerMetadata['provider'] ?? 'xendit'),
                'status' => (string) ($providerMetadata['status'] ?? 'pending'),
                'xendit_status' => $providerMetadata['xendit_status'] ?? null,
                'xendit_invoice_id' => $providerMetadata['xendit_invoice_id'] ?? null,
                'xendit_external_id' => $providerMetadata['xendit_external_id'] ?? null,
                'xendit_invoice_url' => $providerMetadata['xendit_invoice_url'] ?? null,
                'checkout_url' => $checkoutUrl,
                'expires_at' => $providerMetadata['expires_at'] ?? null,
                'success_redirect_url' => $providerMetadata['success_redirect_url'] ?? null,
                'failure_redirect_url' => $providerMetadata['failure_redirect_url'] ?? null,
                'failure_code' => null,
                'failure_message' => null,
            ]);

            return redirect()->to($checkoutUrl);
        } catch (\Throwable $exception) {
            log_message('error', 'Premium checkout initiation failed for course ' . $id . ': ' . $exception->getMessage());

            if ($transaction) {
                $this->paymentTransactionModel->update($transaction['id'], [
                    'status' => 'failed',
                    'failure_code' => 'checkout_init_failed',
                    'failure_message' => $exception->getMessage(),
                ]);
            }

            return redirect()->to(site_url('user/view-course/' . $id . '/checkout'))
                ->with('error', 'Gagal menyiapkan checkout. Silakan coba lagi.');
        } finally {
            $this->paymentTransactionModel->releaseCheckoutLock($userId, (int) $id);
        }
    }

    private function buildPurchaseState(array $course, bool $isEnrolled): array
    {
        $isPremium = $this->isTruthy($course['is_premium'] ?? false);
        $priceAmount = (int) ($course['price_amount'] ?? 0);
        $priceCurrency = strtoupper((string) ($course['price_currency'] ?? 'IDR'));
        $isPublished = ($course['status'] ?? null) === 'published';
        $isPurchasable = $this->isTruthy($course['is_purchasable'] ?? false);
        $hasValidPrice = $priceAmount > 0;

        $checkoutBlockedMessage = null;
        if ($isPremium && !$isEnrolled) {
            if (!$isPublished) {
                $checkoutBlockedMessage = 'Kursus premium ini belum dipublikasikan untuk pembelian.';
            } elseif (!$isPurchasable) {
                $checkoutBlockedMessage = 'Pembelian kursus premium ini sedang tidak tersedia.';
            } elseif (!$hasValidPrice) {
                $checkoutBlockedMessage = 'Harga kursus premium ini belum siap untuk checkout.';
            }
        }

        return [
            'is_premium' => $isPremium,
            'price_amount' => $priceAmount,
            'price_currency' => $priceCurrency,
            'can_checkout' => $isPremium && !$isEnrolled && $isPublished && $isPurchasable && $hasValidPrice,
            'checkout_blocked_message' => $checkoutBlockedMessage,
        ];
    }

    private function createPendingTransaction(array $course): array
    {
        $userId = (int) $this->currentUser['id'];
        $courseId = (int) $course['id'];
        $historicalAttemptCount = $this->paymentTransactionModel->countHistoricalAttempts($userId, $courseId);
        $customerName = trim((string) ($this->currentUser['full_name'] ?? ''));

        if ($customerName === '') {
            $customerName = (string) ($this->currentUser['username'] ?? 'Peserta Kursus');
        }

        $transactionId = $this->paymentTransactionModel->insert([
            'user_id' => $userId,
            'course_id' => $courseId,
            'reference_code' => $this->xenditPaymentLinkService->buildReferenceCode($courseId, $userId, $historicalAttemptCount),
            'provider' => 'xendit',
            'status' => 'pending',
            'amount' => (int) ($course['price_amount'] ?? 0),
            'currency' => strtoupper((string) ($course['price_currency'] ?? 'IDR')),
            'customer_email' => (string) ($this->currentUser['email'] ?? ''),
                'customer_name' => $customerName,
            ], true);

        if (!$transactionId) {
            throw new RuntimeException('Transaksi pending tidak berhasil dibuat.');
        }

        $transaction = $this->paymentTransactionModel->find($transactionId);

        if (!$transaction) {
            throw new RuntimeException('Transaksi pending tidak dapat dimuat kembali.');
        }

        return $transaction;
    }

    private function isTruthy($value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'TRUE', 't', 'T'], true);
    }
}
