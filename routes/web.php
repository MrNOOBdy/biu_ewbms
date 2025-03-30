<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermiController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\Bill_rateController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\Cov_dateController;
use App\Http\Controllers\ConsumerController;
use App\Http\Controllers\LocalSetController;
use App\Http\Controllers\PassResetController;
use App\Http\Controllers\ConnPayController;
use App\Http\Controllers\ApplicationIncomeController;
use App\Http\Controllers\ReportBillController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\BillPayController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\Bill_NoticeController;
use App\Http\Controllers\MRController;
use App\Http\Controllers\MRsBlockCont;
use Illuminate\Support\Facades\Route;

// Start page
Route::get('/', [WelcomeController::class, 'index']);

// Authentication Routes
Route::middleware(['web'])->group(function () {
    Route::get('/login', [AuthController::class, 'show_loginForm'])->name('login');
    
    // Guest only routes
    Route::middleware(['guest'])->group(function () {
        Route::get('/adm_login', [AuthController::class, 'show_loginForm'])->name('adm_login.form');
        Route::post('/adm_login', [AuthController::class, 'adm_login'])->name('adm_login');
        
        // Password reset routes
        Route::controller(PassResetController::class)->group(function () {
            Route::get('/forgot-password-method', 'showForgotMethod')->name('password.method');
            Route::get('/forgot-password', 'showForgotForm')->name('password.request');
            Route::post('/forgot-password', 'sendResetLink')->name('password.email');
            Route::get('/forgot-password-sms', 'showForgotSmsForm')->name('password.request.sms');
            Route::post('/send-password-reset-otp', 'sendResetOtp')->name('password.send.otp');
            Route::get('/verify-reset-otp', 'showVerifyOtpForm')->name('password.verify.otp.form');
            Route::post('/verify-reset-otp', 'verifyResetOtp')->name('password.verify.otp');
            Route::get('/verify-code', 'showVerifyCodeForm')->name('password.verify.code.form');
            Route::post('/verify-code', 'verifyCode')->name('password.verify.code');
        });
    });

    // Authenticated routes
    Route::middleware(['auth'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashController::class, 'dashboard'])->name('dashboard');

        // Log out
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // User management routes
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{id}/activate', [UserController::class, 'activate'])->name('users.activate');
        Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{id}/verify-password', [UserController::class, 'verifyUserPassword'])->name('users.verifyPassword');
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.resetPassword');
        Route::post('/users/{user}/verify-delete-password', [UserController::class, 'verifyDeletePassword'])->name('users.verifyDeletePassword');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Role management routes
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{id}', [RoleController::class, 'edit'])->name('roles.edit');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('/role-permissions/{role_id}', [RoleController::class, 'showPermissions'])->name('role-permissions');
        Route::post('/roles/{role_id}/update-permissions', [RoleController::class, 'updatePermissions'])->name('roles.update-permissions');

        // Local settings routes
        Route::get('/local-settings', [LocalSetController::class, 'index'])->name('local-settings.index');
        Route::post('/update-fees', [LocalSetController::class, 'updateFees'])->name('fees.update');

        // Notification routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NoticeController::class, 'index'])->name('notifications.index');
            Route::post('/', [NoticeController::class, 'store'])->name('notifications.store');
            Route::get('/{id}/edit', [NoticeController::class, 'edit'])->name('notifications.edit');
            Route::put('/{id}', [NoticeController::class, 'update'])->name('notifications.update');
            Route::delete('/{id}', [NoticeController::class, 'destroy'])->name('notifications.destroy');
        });

        // Bill rates routes
        Route::prefix('billRates')->group(function () {
            Route::get('/', [Bill_rateController::class, 'index'])->name('billRates.index');
            Route::post('/', [Bill_rateController::class, 'store'])->name('billRates.store');
            Route::get('/{id}/edit', [Bill_rateController::class, 'edit'])->name('billRates.edit');
            Route::put('/{id}', [Bill_rateController::class, 'update'])->name('billRates.update');
            Route::delete('/{id}', [Bill_rateController::class, 'destroy'])->name('billRates.destroy');
        });

        // Block management routes
        Route::prefix('blocks')->group(function () {
            Route::get('/', [BlockController::class, 'index'])->name('blocks.index');
            Route::post('/', [BlockController::class, 'store'])->name('blocks.store');
            Route::post('/{id}/add-barangay', [BlockController::class, 'addBarangay'])->name('blocks.addBarangay');
            Route::get('/{id}/edit', [BlockController::class, 'edit'])->name('blocks.edit');
            Route::put('/{id}', [BlockController::class, 'update'])->name('blocks.update');
            Route::delete('/{id}', [BlockController::class, 'destroy'])->name('blocks.destroy');
        });

        // Coverage date routes
        Route::prefix('coverage-dates')->group(function () {
            Route::get('/', [Cov_dateController::class, 'index'])->name('coverage-dates.index');
            Route::post('/', [Cov_dateController::class, 'store'])->name('coverage-dates.store');
            Route::put('/{id}', [Cov_dateController::class, 'update'])->name('coverage-dates.update');
            Route::delete('/{id}', [Cov_dateController::class, 'destroy'])->name('coverage-dates.destroy');
            Route::get('/{id}', [Cov_dateController::class, 'show'])->name('coverage-dates.show');
            Route::post('/validate', [Cov_dateController::class, 'validateOverlap'])->name('coverage-dates.validate');
        });

        // Water Consumer Management routes
        Route::prefix('consumers')->group(function () {
            Route::get('/', [ConsumerController::class, 'index'])->name('consumers.index');
            Route::post('/', [ConsumerController::class, 'store'])->name('consumers.store');
            Route::get('/{id}/edit', [ConsumerController::class, 'edit'])->name('consumers.edit');
            Route::put('/{id}', [ConsumerController::class, 'update'])->name('consumers.update');
            Route::delete('/{id}', [ConsumerController::class, 'destroy'])->name('consumers.destroy');
            Route::post('/{id}/status', [ConsumerController::class, 'updateStatus'])->name('consumers.status');
            Route::get('/filter', [ConsumerController::class, 'filter'])->name('consumers.filter');
            Route::get('/generate-id/{blockId}', [ConsumerController::class, 'generateId'])->name('consumers.generateId');
            Route::get('/{id}/view', [ConsumerController::class, 'view'])->name('consumers.view');
            Route::post('/{id}/reconnect', [ConsumerController::class, 'reconnect'])->name('consumers.reconnect');
            Route::get('/check-reconnection-payment/{id}', [ConsumerController::class, 'checkReconnectionPayment'])
                ->name('consumers.check-reconnection-payment');
            Route::get('/check-reconnection-status/{customerId}', [ConsumerController::class, 'checkReconnectionStatus'])
                ->name('check.reconnection.status');
            Route::get('/barangays/{blockId}', [ConsumerController::class, 'getBarangaysByBlock']);
            Route::get('billings/{id}', [ConsumerController::class, 'viewBillings'])->name('consumer.billings');
        });

        // Application payment routes
        Route::get('/application-fee', [ConnPayController::class, 'index'])->name('application.fee');
        Route::post('/process-payment', [ConnPayController::class, 'processPayment'])->name('process.payment');
        Route::get('/check-application-permission', [ConnPayController::class, 'checkPermission']);
        Route::get('/application-income', [ConnPayController::class, 'showIncome'])->name('application.income');
        Route::get('/print-application-receipt/{customer_id}', [ConnPayController::class, 'printReceipt'])->name('application.print');

        // Service routes
        Route::prefix('service')->group(function () {
            Route::get('/', [ServiceController::class, 'index'])->name('service.index');
            Route::post('/process-payment', [ServiceController::class, 'processPayment'])->name('service.process.payment');
            Route::get('/print-receipt/{customer_id}', [ServiceController::class, 'printReceipt'])->name('service.print');
        });

        // Report routes
        Route::get('/appli_income', [ApplicationIncomeController::class, 'index'])->name('appli_income');
        Route::get('/income_rep', [ReportBillController::class, 'income_index'])->name('income_index');
        Route::get('/balance_rep', [ReportBillController::class, 'balance_index'])->name('balance_index');

        // Meter reader reading routes
        Route::get('/meter_read', [MRController::class, 'index'])->name('meter-readings');

        // Billing pay routes
        Route::prefix('billing')->group(function () {
            Route::get('/payments', [BillPayController::class, 'showPayments'])->name('billing.payments');
            Route::post('/readings', [BillPayController::class, 'storeReadings'])->name('billing.store-readings');
            Route::get('/get-bills/{consumerId}', [BillPayController::class, 'getBills'])->name('billing.get-bills');
            Route::get('/get-bill-details/{billId}', [BillPayController::class, 'getBillDetails'])->name('billing.details');
            Route::post('/process-payment', [BillPayController::class, 'processPayment'])->name('billing.process-payment');
            Route::get('/print-receipt/{billId}', [BillPayController::class, 'printReceipt'])->name('billing.print-receipt');
        });

        // Latest bill routes
        Route::get('/latest-bills', [BillingController::class, 'latestBills'])->name('latest-bills');
        Route::get('/billing/get-reading-details/{consreadId}', [BillingController::class, 'getReadingDetails']);
        Route::get('/billing/get-bill-details/{consreadId}', [BillingController::class, 'getBillDetails']);
        Route::post('/billing/add-bill', [BillingController::class, 'addBill']);
        Route::post('/billing/send-bill-sms', [BillingController::class, 'sendBillSMS']);

        // Bill Notice routes
       Route::get('/notice-bill', [Bill_NoticeController::class, 'noticeBill'])->name('notice-bill');
       Route::get('/billing/notice/{id}/details', [Bill_NoticeController::class, 'getBillDetails']);
       Route::post('/billing/notice/send-sms', [Bill_NoticeController::class, 'sendNoticeSMS'])->name('billing.notice.send-sms');

        // Meter reader block assignment routes
        Route::get('/meter-readers/blocks', [MRsBlockCont::class, 'index'])->name('meter-readers.blocks');
        Route::post('/meter-readers/assign-blocks', [MRsBlockCont::class, 'assignBlocks'])->name('meter-readers.assign-blocks');
        Route::get('/meter-readers/{id}/blocks', [MRsBlockCont::class, 'getAssignedBlocks'])->name('meter-readers.get-blocks');
    });
});