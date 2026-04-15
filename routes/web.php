<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Admin\AdminServiceOperationsController;
use App\Http\Controllers\Admin\AdminUserManagementController;
use App\Http\Controllers\Admin\GovernmentOfficeController;
use App\Http\Controllers\Admin\MunicipalityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Citizen\CitizenAppointmentController;
use App\Http\Controllers\Citizen\CitizenDashboardController;
use App\Http\Controllers\Citizen\CitizenFeedbackController;
use App\Http\Controllers\Citizen\CitizenOfficeChatController;
use App\Http\Controllers\Citizen\CitizenOfficeDirectoryController;
use App\Http\Controllers\Citizen\CitizenPaymentController;
use App\Http\Controllers\Citizen\CitizenServiceRequestController;
use App\Http\Controllers\Citizen\IdVerificationController;
use App\Http\Controllers\Office\NotificationController;
use App\Http\Controllers\Office\OfficeAppointmentController;
use App\Http\Controllers\Office\OfficeContextController;
use App\Http\Controllers\Office\OfficeDashboardController;
use App\Http\Controllers\Office\OfficeFeedbackController;
use App\Http\Controllers\Office\OfficeOfficeChatController;
use App\Http\Controllers\Office\OfficePasswordController;
use App\Http\Controllers\Office\OfficeProfileController;
use App\Http\Controllers\Office\OfficeServiceCategoryController;
use App\Http\Controllers\Office\OfficeServiceController;
use App\Http\Controllers\Office\OfficeServiceRequestController;
use App\Http\Controllers\Public\ServiceRequestTrackingController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// ── Root ──────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// Public request tracking (QR / offline — no login; token is the same as ServiceRequest.qr_code)
Route::get('/track/{token}', [ServiceRequestTrackingController::class, 'show'])
    ->where('token', '[A-Za-z0-9\-]+')
    ->middleware('throttle:120,1')
    ->name('requests.track');

Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle'])
    ->middleware('throttle:120,1')
    ->name('webhooks.stripe');

// ── Guest-only routes ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.attempt');

    // Social Login
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

});

// ── Password Reset (no auth required) ────────────────────────────────────────
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// ── Authenticated routes ──────────────────────────────────────────────────────
Route::middleware(['auth', 'active'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');

    // 2FA routes (no 2fa_verified required — these build that state)
    Route::get('/2fa/setup', [AuthController::class, 'show2faSetup'])->name('2fa.setup');
    Route::post('/2fa/setup', [AuthController::class, 'confirm2faSetup'])->name('2fa.setup.confirm');
    Route::get('/2fa/verify', [AuthController::class, 'show2faVerify'])->name('2fa.verify');
    Route::post('/2fa/verify', [AuthController::class, 'verify2fa'])->name('2fa.verify.confirm');

    // ── Routes requiring 2FA ─────────────────────────────────────────────────
    Route::middleware(['2fa'])->group(function () {

        Route::middleware('role:admin')->group(function () {
            Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
            Route::resource('/admin/municipalities', MunicipalityController::class)
                ->except(['show'])
                ->names('admin.municipalities');
            Route::resource('/admin/offices', GovernmentOfficeController::class)
                ->except(['show'])
                ->parameters(['offices' => 'office'])
                ->names('admin.offices');

            Route::get('/admin/office-users', [AdminUserManagementController::class, 'officeUsersIndex'])
                ->name('admin.office-users.index');
            Route::post('/admin/office-users', [AdminUserManagementController::class, 'officeUsersStore'])
                ->name('admin.office-users.store');
            Route::get('/admin/office-users/{user}/edit', [AdminUserManagementController::class, 'officeUsersEdit'])
                ->name('admin.office-users.edit');
            Route::put('/admin/office-users/{user}', [AdminUserManagementController::class, 'officeUsersUpdate'])
                ->name('admin.office-users.update');
            Route::post('/admin/office-users/{user}/password-reset', [AdminUserManagementController::class, 'officeUsersSendPasswordReset'])
                ->name('admin.office-users.password-reset');
            Route::patch('/admin/office-users/{user}/toggle-active', [AdminUserManagementController::class, 'officeUsersToggleActive'])
                ->name('admin.office-users.toggle-active');

            Route::get('/admin/citizens', [AdminUserManagementController::class, 'citizensIndex'])
                ->name('admin.citizens.index');
            Route::patch('/admin/citizens/{user}/toggle-active', [AdminUserManagementController::class, 'citizensToggleActive'])
                ->name('admin.citizens.toggle-active');

            Route::get('/admin/service-requests', [AdminServiceOperationsController::class, 'index'])
                ->name('admin.service-requests.index');
            Route::get('/admin/service-requests/{serviceRequest}', [AdminServiceOperationsController::class, 'show'])
                ->name('admin.service-requests.show');

            Route::get('/admin/reports', [AdminReportsController::class, 'index'])
                ->name('admin.reports.index');
        });

        Route::middleware('role:office_user')->group(function () {
            Route::get('/office/dashboard', [OfficeDashboardController::class, 'index'])->name('office.dashboard');
            Route::post('/office/context', [OfficeContextController::class, 'update'])->name('office.context');

            Route::get('/office/profile', [OfficeProfileController::class, 'index'])->name('office.profile.index');
            Route::get('/office/profile/{office}/edit', [OfficeProfileController::class, 'edit'])
                ->middleware('office.access')
                ->name('office.profile.edit');
            Route::put('/office/profile/{office}', [OfficeProfileController::class, 'update'])
                ->middleware('office.access')
                ->name('office.profile.update');

            Route::middleware('office.access')->group(function () {
                Route::get('/office/{office}/requests', [OfficeServiceRequestController::class, 'index'])
                    ->name('office.requests.index');
                Route::get('/office/{office}/requests/{serviceRequest}', [OfficeServiceRequestController::class, 'show'])
                    ->name('office.requests.show');
                Route::patch('/office/{office}/requests/{serviceRequest}/status', [OfficeServiceRequestController::class, 'updateStatus'])
                    ->name('office.requests.status');
                Route::post('/office/{office}/requests/{serviceRequest}/documents', [OfficeServiceRequestController::class, 'storeDocument'])
                    ->name('office.requests.documents.store');

                Route::get('/office/{office}/chat', [OfficeOfficeChatController::class, 'index'])
                    ->name('office.chat.index');
                Route::get('/office/{office}/chat/{citizen}', [OfficeOfficeChatController::class, 'show'])
                    ->name('office.chat.show');
                Route::post('/office/{office}/chat/{citizen}', [OfficeOfficeChatController::class, 'store'])
                    ->middleware('throttle:60,1')
                    ->name('office.chat.store');

                Route::get('/office/{office}/feedback', [OfficeFeedbackController::class, 'index'])
                    ->name('office.feedback.index');
                Route::get('/office/{office}/feedback/{feedback}/edit', [OfficeFeedbackController::class, 'edit'])
                    ->name('office.feedback.edit');
                Route::patch('/office/{office}/feedback/{feedback}', [OfficeFeedbackController::class, 'updateReply'])
                    ->name('office.feedback.reply');

                Route::resource('/office/{office}/categories', OfficeServiceCategoryController::class)
                    ->except(['show'])
                    ->parameters(['categories' => 'category'])
                    ->names('office.categories');
                Route::resource('/office/{office}/services', OfficeServiceController::class)
                    ->except(['show'])
                    ->parameters(['services' => 'service'])
                    ->names('office.services');

                Route::get('/office/{office}/time-slots', [OfficeAppointmentController::class, 'index'])
                    ->name('office.slots.index');
                Route::post('/office/{office}/time-slots', [OfficeAppointmentController::class, 'storeSlot'])
                    ->name('office.slots.store');
                Route::delete('/office/{office}/time-slots/{slot}', [OfficeAppointmentController::class, 'destroySlot'])
                    ->name('office.slots.destroy');

                Route::get('/office/{office}/appointments', [OfficeAppointmentController::class, 'appointments'])
                    ->name('office.appointments.index');
                Route::patch('/office/{office}/appointments/{appointment}/confirm', [OfficeAppointmentController::class, 'confirmAppointment'])
                    ->name('office.appointments.confirm');
                Route::patch('/office/{office}/appointments/{appointment}/cancel', [OfficeAppointmentController::class, 'cancelAppointment'])
                    ->name('office.appointments.cancel');
            });

            Route::get('/office/change-password', [OfficePasswordController::class, 'showChangeForm'])->name('office.password.change');
            Route::post('/office/change-password', [OfficePasswordController::class, 'update'])->name('office.password.update');

            // Notifications
            Route::get('/office/notifications', [NotificationController::class, 'index'])->name('office.notifications.index');
            Route::post('/office/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('office.notifications.read');
            Route::post('/office/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('office.notifications.read-all');
        });

        Route::middleware('role:citizen')->group(function () {
            Route::get('/citizen/dashboard', [CitizenDashboardController::class, 'index'])->name('citizen.dashboard');
            Route::get('/citizen/offices', [CitizenOfficeDirectoryController::class, 'index'])->name('citizen.offices.index');
            Route::get('/citizen/offices/{office}', [CitizenOfficeDirectoryController::class, 'show'])->name('citizen.offices.show');
            Route::get('/citizen/offices/{office}/chat', [CitizenOfficeChatController::class, 'index'])->name('citizen.offices.chat');
            Route::post('/citizen/offices/{office}/chat', [CitizenOfficeChatController::class, 'store'])
                ->middleware('throttle:60,1')
                ->name('citizen.offices.chat.store');
            Route::get('/citizen/offices/{office}/services/{service}/apply', [CitizenServiceRequestController::class, 'create'])
                ->name('citizen.services.apply');
            Route::post('/citizen/offices/{office}/services/{service}/apply', [CitizenServiceRequestController::class, 'store'])
                ->name('citizen.services.apply.store');
            Route::get('/citizen/requests', [CitizenServiceRequestController::class, 'index'])->name('citizen.requests.index');
            Route::get('/citizen/requests/{serviceRequest}', [CitizenServiceRequestController::class, 'show'])->name('citizen.requests.show');
            Route::get('/citizen/requests/{serviceRequest}/pay', [CitizenPaymentController::class, 'pay'])->name('citizen.requests.pay');
            Route::post('/citizen/requests/{serviceRequest}/pay/stripe/confirm', [CitizenPaymentController::class, 'confirmStripeElements'])
                ->middleware('throttle:30,1')
                ->name('citizen.requests.pay.stripe.confirm');
            Route::get('/citizen/payments/stripe/elements-return', [CitizenPaymentController::class, 'stripeElementsReturn'])->name('citizen.payments.stripe.elements-return');
            Route::get('/citizen/payments/stripe/return', [CitizenPaymentController::class, 'stripeReturn'])->name('citizen.payments.stripe.return');
            Route::post('/citizen/requests/{serviceRequest}/pay/crypto-quote', [CitizenPaymentController::class, 'cryptoQuote'])
                ->middleware('throttle:30,1')
                ->name('citizen.requests.pay.crypto-quote');
            Route::post('/citizen/requests/{serviceRequest}/pay/crypto-confirm', [CitizenPaymentController::class, 'cryptoConfirm'])
                ->middleware('throttle:20,1')
                ->name('citizen.requests.pay.crypto-confirm');
            Route::post('/citizen/requests/{serviceRequest}/documents', [CitizenServiceRequestController::class, 'storeAdditionalDocuments'])
                ->middleware('throttle:30,1')
                ->name('citizen.requests.documents.store');
            Route::get('/citizen/requests/{serviceRequest}/feedback', [CitizenFeedbackController::class, 'createForRequest'])
                ->name('citizen.feedback.request.create');
            Route::post('/citizen/requests/{serviceRequest}/feedback', [CitizenFeedbackController::class, 'storeForRequest'])
                ->name('citizen.feedback.request.store');

            Route::get('/citizen/offices/{office}/feedback', [CitizenFeedbackController::class, 'createForOffice'])
                ->name('citizen.feedback.office.create');
            Route::post('/citizen/offices/{office}/feedback', [CitizenFeedbackController::class, 'storeForOffice'])
                ->name('citizen.feedback.office.store');

            Route::get('/citizen/offices/{office}/appointments', [CitizenAppointmentController::class, 'book'])->name('citizen.appointments.book');
            Route::post('/citizen/offices/{office}/appointments/{slot}', [CitizenAppointmentController::class, 'store'])->name('citizen.appointments.store');
            Route::patch('/citizen/offices/{office}/appointments/{appointment}/cancel', [CitizenAppointmentController::class, 'cancel'])
                ->name('citizen.appointments.cancel');

            Route::get('/citizen/id-verify', [IdVerificationController::class, 'show'])->name('citizen.id.verify');
            Route::post('/citizen/id-verify', [IdVerificationController::class, 'upload'])->name('citizen.id.upload');
            Route::post('/citizen/id-save', [IdVerificationController::class, 'save'])->name('citizen.id.save');

            Route::get('/citizen/notifications', [NotificationController::class, 'index'])->name('citizen.notifications.index');
            Route::post('/citizen/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('citizen.notifications.read');
            Route::post('/citizen/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('citizen.notifications.read-all');
        });
    });
});

require __DIR__.'/municipality.php';
