<?php

namespace Config;

use App\Controllers\CourseController2;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\CourseController as AdminCourseController;
use App\Controllers\Admin\ModuleController;
use App\Controllers\Admin\LessonController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\CourseReviewController as AdminCourseReviewController;
use App\Controllers\Admin\UserController;
use App\Controllers\Admin\EnrollmentController;
use App\Controllers\Admin\PaymentController;
use App\Controllers\UserProfileController;
// User Controllers
use App\Controllers\Users\DashboardController as UserDashboardController;
use App\Controllers\Users\CourseController as UserCourseController;
use App\Controllers\Users\ReviewController;
use App\Controllers\UserSettingsController;
use App\Controllers\PaymentCallbackController;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
* --------------------------------------------------------------------
* Router Setup
* --------------------------------------------------------------------
*/
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
* --------------------------------------------------------------------
* Route Definitions
* --------------------------------------------------------------------
*/

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

// Auth routes
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::attemptLogin');
$routes->get('/signup', 'AuthController::signup');
$routes->post('/signup', 'AuthController::attemptSignup');
$routes->get('/logout', 'AuthController::logout');

// Forgot / reset password routes
$routes->get('/forgot-password', 'AuthController::forgotPassword');
$routes->post('/forgot-password', 'AuthController::sendResetLink');
$routes->get('/reset-password/(:segment)', 'AuthController::resetPassword/$1');
$routes->post('/reset-password/(:segment)', 'AuthController::updatePassword/$1');

// Course routes - public
$routes->get('/course', [CourseController2::class, 'index']);
$routes->post('/api/payments/webhook', [PaymentCallbackController::class, 'xenditWebhook']);

// Course routes - requires login
$routes->group('course', ['filter' => 'user'], function($routes) {
    $routes->get('lesson/(:num)/(:num)', [CourseController2::class, 'lesson/$1/$2']);
    $routes->post('mark-complete', [CourseController2::class, 'markComplete']);
    $routes->post('update-progress', [CourseController2::class, 'updateProgress']);
    $routes->get('(:num)', [CourseController2::class, 'redirectCourse/$1']);
    $routes->get('(:num)/lesson/(:num)', [CourseController2::class, 'courseById/$1/$2']);
    $routes->get('(:num)/enroll', [CourseController2::class, 'enroll/$1']);
});

// api for client side
$routes->group('api', ['filter' => 'user'], function ($routes) {
    $routes->get('mark-complete/(:num)/(:num)', [CourseController2::class, 'markCourseCompleted/$1/$2']);
    $routes->get('lesson-navigation/(:num)/(:num)', [CourseController2::class, 'getLessonNavigation/$1/$2']);
});

// User Dashboard Routes
$routes->group('user', ['filter' => 'user'], function($routes) {
    // Dashboard
    $routes->get('dashboard', [UserDashboardController::class, 'index']);
    $routes->get('payment-history', [UserDashboardController::class, 'paymentHistory']);
    $routes->get('payment-history/invoice/(:num)', [UserDashboardController::class, 'invoice/$1']);
    
    // Courses
    $routes->get('courses', [UserCourseController::class, 'index']);
    $routes->get('courses/enrolled', [UserCourseController::class, 'enrolled']);
    $routes->get('view-course/(:num)', [UserCourseController::class, 'viewCourse/$1']);
    $routes->get('view-course/(:num)/checkout', [UserCourseController::class, 'checkout/$1']);
    $routes->post('view-course/(:num)/checkout', [UserCourseController::class, 'startCheckout/$1']);
    $routes->get('payments/xendit/return', [PaymentCallbackController::class, 'xenditReturn']);
    $routes->get('payments/xendit/failure', [PaymentCallbackController::class, 'xenditFailureReturn']);
    
    $routes->get('profile', [UserProfileController::class, 'index']);
    $routes->get('profile/edit', [UserProfileController::class, 'edit']);
    $routes->post('profile/update', [UserProfileController::class, 'update']);
    
// settings 
    $routes->get('settings', [UserSettingsController::class, 'index']);
    $routes->post('settings', [UserSettingsController::class, 'update']);

});

$routes->group('payment', ['filter' => 'user'], function($routes) {
    $routes->get('xendit/success', [PaymentCallbackController::class, 'xenditSuccessRedirect']);
    $routes->get('xendit/failure', [PaymentCallbackController::class, 'xenditFailureRedirect']);
});

// Course review routes
$routes->group('course', static function($routes) {
    $routes->get('(:num)/reviews', [ReviewController::class, 'index']);
    $routes->get('(:num)/reviews/create', [ReviewController::class, 'create']);
    $routes->post('(:num)/reviews', [ReviewController::class, 'store']);
    $routes->get('(:num)/reviews/edit', [ReviewController::class, 'edit']);
    $routes->post('(:num)/reviews/update', [ReviewController::class, 'update']);
    $routes->get('(:num)/reviews/delete', [ReviewController::class, 'delete']);
});

// Certificate route - Login required
$routes->get('certificate/(:num)', 'Users\CertificateController::generate/$1', ['filter' => 'user']);

// Admin routes
$routes->group('admin', ['filter' => 'admin'], function($routes) {
    // Dashboard
    $routes->get('/', [DashboardController::class, 'index']);
    $routes->get('dashboard', [DashboardController::class, 'index']);
    
    // TODO: GET COURSE REVIEW BY ID
    $routes->get('course/(:num)/reviews', [AdminCourseReviewController::class, 'index/$1']);
    $routes->get('course/(:num)/reviews/(:num)/delete', [AdminCourseReviewController::class, 'delete/$1/$2']);

    // Courses
    $routes->get('course', [AdminCourseController::class, 'index']);
    $routes->get('course/create', [AdminCourseController::class, 'create']);
    $routes->post('course', [AdminCourseController::class, 'store']);
    $routes->get('course/(:num)/edit', [AdminCourseController::class, 'edit/$1']);
    $routes->post('course/(:num)', [AdminCourseController::class, 'update/$1']);
    $routes->get('course/(:num)/delete', [AdminCourseController::class, 'delete/$1']);
    
    // Modules
    $routes->get('course/(:num)/modules', [ModuleController::class, 'index/$1']);
    $routes->get('course/(:num)/modules/create', [ModuleController::class, 'create/$1']);
    $routes->post('course/(:num)/modules', [ModuleController::class, 'store/$1']);
    $routes->get('course/(:num)/modules/(:num)/edit', [ModuleController::class, 'edit/$1/$2']);
    $routes->post('course/(:num)/modules/(:num)', [ModuleController::class, 'update/$1/$2']);
    $routes->get('course/(:num)/modules/(:num)/delete', [ModuleController::class, 'delete/$1/$2']);
    
    // Lessons
    $routes->get('course/(:num)/modules/(:num)/lessons', [LessonController::class, 'index/$1/$2']);
    $routes->get('course/(:num)/modules/(:num)/lessons/create', [LessonController::class, 'create/$1/$2']);
    $routes->post('course/(:num)/modules/(:num)/lessons', [LessonController::class, 'store/$1/$2']);
    $routes->get('course/(:num)/modules/(:num)/lessons/(:num)/edit', [LessonController::class, 'edit/$1/$2/$3']);
    $routes->post('course/(:num)/modules/(:num)/lessons/(:num)', [LessonController::class, 'update/$1/$2/$3']);
    $routes->get('course/(:num)/modules/(:num)/lessons/(:num)/delete', [LessonController::class, 'delete/$1/$2/$3']);
    
    // Categories
    $routes->get('categories', [CategoryController::class, 'index']);
    $routes->get('categories/create', [CategoryController::class, 'create']);
    $routes->post('categories', [CategoryController::class, 'store']);
    $routes->get('categories/(:num)/edit', [CategoryController::class, 'edit/$1']);
    $routes->post('categories/(:num)', [CategoryController::class, 'update/$1']);
    $routes->get('categories/(:num)/delete', [CategoryController::class, 'delete/$1']);
    
    // Users
    $routes->get('users', [UserController::class, 'index']);
    $routes->get('users/create', [UserController::class, 'create']);
    $routes->post('users', [UserController::class, 'store']);
    $routes->get('users/(:num)/edit', [UserController::class, 'edit/$1']);
    $routes->post('users/(:num)', [UserController::class, 'update/$1']);
    $routes->get('users/(:num)/delete', [UserController::class, 'delete/$1']);
    
    // Enrollments
    $routes->get('enrollments', [EnrollmentController::class, 'index']);
    $routes->get('enrollments/(:num)', [EnrollmentController::class, 'show/$1']);

    // Payments
    $routes->get('payments', [PaymentController::class, 'index']);
    $routes->get('payments/(:num)', [PaymentController::class, 'show/$1']);

    // Admin API routes for AJAX operations
    $routes->group('api', function($routes) {
        // Course operations
        $routes->post('courses/(:num)/status', [AdminCourseController::class, 'updateStatus/$1']);
        $routes->post('courses/(:num)/feature', [AdminCourseController::class, 'toggleFeatured/$1']);
        
        // Module operations
        $routes->post('courses/(:num)/modules/reorder', [ModuleController::class, 'reorder/$1']);
        
        // Lesson operations
        $routes->post('courses/(:num)/modules/(:num)/lessons/reorder', [LessonController::class, 'reorder/$1/$2']);
        
        // Dashboard stats
        $routes->get('stats/overview', [DashboardController::class, 'getOverviewStats']);
        $routes->get('stats/enrollments', [DashboardController::class, 'getEnrollmentStats']);
    });
});


/*
* --------------------------------------------------------------------
* Additional Routing
* --------------------------------------------------------------------
*
* There will often be times that you need additional routing and you
* need it to be able to override any defaults in this file. Environment
* based routes is one such time. require() additional route files here
* to make that happen.
*
* You will have access to the $routes object within that file without
* needing to reload it.
*/
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
