<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Http\Controllers\Admin\AdminServiceRequestController;
use App\Http\Controllers\Admin\AdminTechnicianController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminSystemOperationsController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminMessengerController;
use App\Http\Controllers\Admin\AdminIncidentReportController;
use App\Http\Controllers\Admin\AdminPreventiveMaintenanceChecklistController;
use App\Http\Controllers\Admin\AdminOperationsController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\IctIncidentReportController;
use App\Http\Controllers\PreventiveMaintenanceChecklistController;
use App\Http\Controllers\ServiceRequestDraftController;
use App\Http\Controllers\Auth\TwoFactorAuthController;
use App\Http\Controllers\HealthController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/health', HealthController::class)->middleware('throttle:60,1')->name('health');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', '2fa', 'throttle:authenticated'])->group(function () {

    Route::get('/dashboard', [UserDashboardController::class, 'index'])
        ->name('dashboard');

    // PDF Routes (MUST come BEFORE resource route!)
    Route::get('/request/{id}/print', [UserDashboardController::class, 'print'])
        ->name('request.print');
    Route::get('/request/{id}/pdf', [UserDashboardController::class, 'viewPDF'])
        ->name('request.pdf');
    Route::get('/request/{id}/download-pdf', [UserDashboardController::class, 'downloadPDF'])
        ->name('request.download-pdf');
    Route::get('/requests/{request}/attachment', [RequestController::class, 'downloadAttachment'])
        ->name('requests.attachment');
    Route::get('/requests/{request}/attachments/{attachment}', [RequestController::class, 'downloadUploadedAttachment'])
        ->name('requests.attachments.download');
    Route::delete('/requests/{request}/attachments/{attachment}', [RequestController::class, 'destroyUploadedAttachment'])
        ->name('requests.attachments.destroy');
    Route::post('/requests/{request}/feedback', [RequestController::class, 'submitFeedback'])
        ->middleware('throttle:forms')
        ->name('requests.feedback');
    Route::post('/requests/{request}/confirm-resolution', [RequestController::class, 'confirmResolution'])
        ->name('requests.confirm-resolution');
    Route::post('/requests/{request}/reopen', [RequestController::class, 'reopen'])
        ->name('requests.reopen');
    Route::post('/requests/{request}/clarifications/{clarification}/answer', [RequestController::class, 'answerClarification'])
        ->middleware('throttle:uploads')
        ->name('requests.clarifications.answer');
    Route::post('/requests/{request}/schedule-response', [RequestController::class, 'respondToSchedule'])
        ->name('requests.schedule-response');
    Route::get('/requests/{request}/clarification-attachments/{attachment}', [RequestController::class, 'downloadClarificationAttachment'])
        ->name('requests.clarification-attachments.download');
    Route::post('/api/requests/duplicate-check', [RequestController::class, 'checkDuplicates'])
        ->middleware('throttle:forms')
        ->name('requests.duplicate-check');

    Route::resource('requests', RequestController::class)->except(['store', 'update', 'destroy']);
    Route::post('/requests', [RequestController::class, 'store'])->middleware('throttle:uploads')->name('requests.store');
    Route::match(['put', 'patch'], '/requests/{serviceRequest}', [RequestController::class, 'update'])->middleware('throttle:uploads')->name('requests.update');
    Route::delete('/requests/{request}', [RequestController::class, 'destroy'])->middleware('throttle:forms')->name('requests.destroy');

    // User panel routes
    Route::get('/user/profile', [ProfileController::class, 'show'])->name('user.profile');
    Route::get('/user/settings', [ProfileController::class, 'settings'])->name('user.settings');
    Route::post('/user/settings/profile', [ProfileController::class, 'updateProfile'])->middleware('throttle:6,1')->name('settings.updateProfile');
    Route::post('/user/settings/password', [ProfileController::class, 'updatePassword'])->middleware('throttle:5,1')->name('settings.updatePassword');
    Route::post('/user/settings/notifications', [ProfileController::class, 'updateNotifications'])->name('settings.updateNotifications');

    // 2FA Routes
    Route::post('/user/settings/2fa/setup', [TwoFactorAuthController::class, 'beginSettingsSetup'])
        ->middleware('throttle:5,1')->name('settings.2fa.setup');
    Route::post('/user/settings/2fa/enable', [TwoFactorAuthController::class, 'confirmSettingsSetup'])
        ->middleware('throttle:5,1')->name('settings.2fa.enable');
    Route::get('/user/settings/2fa/recovery-codes', [TwoFactorAuthController::class, 'recoveryCodes'])
        ->middleware('password.confirm')->name('settings.2fa.recovery-codes');
    Route::post('/user/settings/2fa/recovery-codes', [TwoFactorAuthController::class, 'regenerateRecoveryCodes'])
        ->middleware('throttle:5,1')->name('settings.2fa.recovery-codes.regenerate');
    Route::post('/user/settings/2fa/disable', [TwoFactorAuthController::class, 'disableSettings'])
        ->middleware('throttle:5,1')->name('settings.2fa.disable');
    Route::delete('/user/settings/sessions/{session}', [ProfileController::class, 'terminateSession'])
        ->name('settings.sessions.destroy');
    Route::post('/user/settings/sessions/logout-others', [ProfileController::class, 'terminateOtherSessions'])
        ->middleware('throttle:5,1')->name('settings.sessions.logout-others');

    Route::get('/user/security', [ProfileController::class, 'security'])->name('user.security');
    Route::post('/user/password', [ProfileController::class, 'updatePassword'])->name('user.password.update');
    Route::get('/user/messenger', [App\Http\Controllers\MessageController::class, 'userMessenger'])->name('user.messenger');

    // API endpoints for AJAX/modals
    Route::get('/api/user/notifications', [UserDashboardController::class, 'getNotifications'])
        ->name('user.notifications');
    Route::get('/user/notifications', [UserDashboardController::class, 'notificationsPage'])
        ->name('user.notifications.index');
    Route::post('/api/notifications/read-all', [UserDashboardController::class, 'markAllNotificationsRead'])
        ->middleware('throttle:forms')
        ->name('notifications.read-all');
    Route::post('/api/notifications/{id}/read', [UserDashboardController::class, 'markNotificationRead'])
        ->name('notification.read');
    Route::delete('/api/notifications', [UserDashboardController::class, 'clearNotifications'])
        ->middleware('throttle:forms')
        ->name('notifications.clear');
    Route::delete('/api/notifications/{id}', [UserDashboardController::class, 'deleteNotification'])
        ->name('notification.delete');
    Route::get('/api/user/login-history', [UserDashboardController::class, 'getLoginHistory'])
        ->name('user.login-history');
    Route::get('/api/request-draft', [ServiceRequestDraftController::class, 'show'])->name('request-draft.show');
    Route::put('/api/request-draft', [ServiceRequestDraftController::class, 'store'])->name('request-draft.store');
    Route::delete('/api/request-draft', [ServiceRequestDraftController::class, 'destroy'])->name('request-draft.destroy');

    // Messenger API Endpoints (User)
    Route::get('/api/messages/request/{id}', [\App\Http\Controllers\MessageController::class, 'getMessages']);
    Route::get('/api/messages/request/{id}/new', [\App\Http\Controllers\MessageController::class, 'getNewMessages']);
    Route::post('/api/messages/send', [\App\Http\Controllers\MessageController::class, 'sendMessage'])->middleware('throttle:messages');
    Route::post('/api/messages/{id}/read', [\App\Http\Controllers\MessageController::class, 'markAsRead']);
    Route::get('/api/messages/request/{id}/unread', [\App\Http\Controllers\MessageController::class, 'getUnreadCount']);
    Route::get('/messages/attachments/{attachment}', [\App\Http\Controllers\MessageController::class, 'downloadAttachment'])
        ->name('messages.attachments.download');
});

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', '2fa', 'throttle:authenticated'])->group(function () {

    // ICT Incident Reports
    Route::resource('incident-reports', IctIncidentReportController::class)->except(['store', 'destroy']);
    Route::post('incident-reports', [IctIncidentReportController::class, 'store'])->middleware('throttle:forms')->name('incident-reports.store');
    Route::get('incident-reports/{incidentReport}/pdf', [IctIncidentReportController::class, 'generatePdf'])
        ->name('incident-reports.pdf');

    // Preventive Maintenance Checklists are MIS-managed records. Clients may
    // review them, while new maintenance work starts as a service request.
    Route::get('pm-checklists', [PreventiveMaintenanceChecklistController::class, 'index'])
        ->name('pm-checklists.index');
    Route::get('pm-checklists/create', [PreventiveMaintenanceChecklistController::class, 'create'])
        ->name('pm-checklists.create');
    Route::get('pm-checklists/{pmChecklist}/pdf', [PreventiveMaintenanceChecklistController::class, 'generatePdf'])
        ->name('pm-checklists.pdf');
    Route::get('pm-checklists/{pmChecklist}', [PreventiveMaintenanceChecklistController::class, 'show'])
        ->name('pm-checklists.show');
});


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin', '2fa', 'throttle:authenticated'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/operations', [AdminOperationsController::class, 'index'])
            ->name('operations');

        Route::get('/requests', [AdminRequestController::class, 'index'])
            ->name('requests.index');

        Route::get('/service-requests/create-on-behalf', [AdminServiceRequestController::class, 'createOnBehalf'])
            ->name('service-requests.create-on-behalf');
        Route::post('/service-requests/create-on-behalf', [AdminServiceRequestController::class, 'storeOnBehalf'])
            ->middleware('throttle:uploads')
            ->name('service-requests.store-on-behalf');

        Route::get('/service-requests/{serviceRequest}/edit', [AdminServiceRequestController::class, 'edit'])
            ->name('service-requests.edit');
        Route::put('/service-requests/{serviceRequest}', [AdminServiceRequestController::class, 'update'])
            ->middleware('throttle:uploads')
            ->name('service-requests.update');

        Route::get('/service-requests/{serviceRequest}', [AdminServiceRequestController::class, 'show'])
            ->name('service-requests.show');

        Route::post('/requests/{serviceRequest}/archive', [AdminRequestController::class, 'archive'])
            ->name('requests.archive');
        Route::post('/requests/{serviceRequest}/restore', [AdminRequestController::class, 'restore'])
            ->withTrashed()
            ->name('requests.restore');

        Route::post('/service-requests/{serviceRequest}/update-status', [AdminServiceRequestController::class, 'updateStatus'])
            ->name('service-requests.update-status');

        Route::post('/service-requests/{serviceRequest}/mark-completed', [AdminServiceRequestController::class, 'markCompleted'])
            ->name('service-requests.mark-completed');

        Route::post('/service-requests/{serviceRequest}/reject', [AdminServiceRequestController::class, 'reject'])
            ->name('service-requests.reject');

        Route::post('/service-requests/{serviceRequest}/triage', [AdminServiceRequestController::class, 'triage'])
            ->name('service-requests.triage');

        Route::post('/service-requests/{serviceRequest}/close', [AdminServiceRequestController::class, 'close'])
            ->name('service-requests.close');

        Route::post('/service-requests/{serviceRequest}/reopen', [AdminServiceRequestController::class, 'reopen'])
            ->name('service-requests.reopen');

        Route::post('/service-requests/{serviceRequest}/feedback-follow-up', [AdminServiceRequestController::class, 'updateFeedbackFollowUp'])
            ->name('service-requests.feedback-follow-up');
        Route::post('/service-requests/{serviceRequest}/clarification', [AdminServiceRequestController::class, 'askClarification'])
            ->name('service-requests.clarification');
        Route::post('/service-requests/{serviceRequest}/client-update', [AdminServiceRequestController::class, 'publishClientUpdate'])
            ->name('service-requests.client-update');
        Route::post('/service-requests/{serviceRequest}/schedule-visit', [AdminServiceRequestController::class, 'scheduleVisit'])
            ->name('service-requests.schedule-visit');
        Route::get('/service-requests/{request}/clarification-attachments/{attachment}', [RequestController::class, 'downloadClarificationAttachment'])
            ->name('service-requests.clarification-attachments.download');

        Route::get('/service-requests/{id}/pdf', [AdminServiceRequestController::class, 'viewPDF'])
            ->name('service-requests.pdf');

        Route::get('/service-requests/{id}/download-pdf', [AdminServiceRequestController::class, 'downloadPDF'])
            ->name('service-requests.download-pdf');

        Route::resource('technicians', AdminTechnicianController::class);
        Route::post('/technicians/{technician}/activate', [AdminTechnicianController::class, 'activate'])
            ->name('technicians.activate');

        Route::get('/technicians/{id}/details', [AdminTechnicianController::class, 'getDetails'])
            ->name('technicians.details');

        Route::resource('users', AdminUserController::class);
        Route::post('/users/{user}/activate', [AdminUserController::class, 'activate'])
            ->name('users.activate');
        Route::post('/users/{user}/unlock', [AdminUserController::class, 'unlock'])
            ->middleware('throttle:sensitive')
            ->name('users.unlock');
        Route::post('/users/{user}/send-password-reset', [AdminUserController::class, 'sendPasswordReset'])
            ->middleware('throttle:sensitive')
            ->name('users.send-password-reset');
        Route::post('/users/{user}/force-logout', [AdminUserController::class, 'forceLogout'])
            ->middleware('throttle:sensitive')
            ->name('users.force-logout');
        Route::post('/users/{user}/require-password-change', [AdminUserController::class, 'requirePasswordChange'])
            ->middleware('throttle:sensitive')
            ->name('users.require-password-change');

        Route::get('/users/{id}/details', [AdminUserController::class, 'getDetails'])
            ->name('users.details');

        Route::get('/messenger', [AdminMessengerController::class, 'index'])
            ->name('messenger');
        Route::get('/api/messages/unread-summary', [AdminMessengerController::class, 'unreadSummary'])
            ->name('messenger.unread-summary');
        Route::get('/messages/attachments/{attachment}', [\App\Http\Controllers\MessageController::class, 'downloadAttachment'])
            ->name('messages.attachments.download');


        Route::get('/settings', [AdminSettingsController::class, 'index'])
            ->name('settings');

        Route::get('/settings/operations', [AdminSystemOperationsController::class, 'index'])
            ->name('settings.operations');
        Route::post('/settings/operations/backup', [AdminSystemOperationsController::class, 'backup'])
            ->middleware('throttle:sensitive')
            ->name('settings.operations.backup');
        Route::post('/settings/operations/privacy-prune', [AdminSystemOperationsController::class, 'privacyPrune'])
            ->middleware('throttle:sensitive')
            ->name('settings.operations.privacy-prune');

        Route::patch('/settings', [AdminSettingsController::class, 'update'])
            ->name('settings.update');
        Route::post('/settings/test-email', [AdminSettingsController::class, 'sendTestEmail'])
            ->middleware('throttle:5,1')
            ->name('settings.test-email');

        Route::get('/api/notifications', [AdminDashboardController::class, 'getNotifications'])
            ->name('notifications');
        Route::get('/notifications', [AdminDashboardController::class, 'notificationsPage'])
            ->name('notifications.index');
        Route::post('/notifications/{notification}/retry', [AdminDashboardController::class, 'retryNotification'])
            ->name('notifications.retry');
        Route::delete('/api/notifications', [AdminDashboardController::class, 'clearNotifications'])
            ->name('notifications.clear');
        Route::post('/api/notifications/{notification}/read', [AdminDashboardController::class, 'markNotificationRead'])
            ->name('notifications.read');
        Route::delete('/api/notifications/{notification}', [AdminDashboardController::class, 'deleteNotification'])
            ->name('notifications.delete');

        Route::get('/incident-reports/{incidentReport}/pdf', [AdminIncidentReportController::class, 'pdf'])
            ->name('incident-reports.pdf');
        Route::post('/incident-reports/{incidentReport}/restore', [AdminIncidentReportController::class, 'restore'])
            ->name('incident-reports.restore');
        Route::resource('incident-reports', AdminIncidentReportController::class);

        Route::get('/pm-checklists/{pmChecklist}/pdf', [AdminPreventiveMaintenanceChecklistController::class, 'pdf'])
            ->name('pm-checklists.pdf');
        Route::post('/pm-checklists/{pmChecklist}/restore', [AdminPreventiveMaintenanceChecklistController::class, 'restore'])
            ->name('pm-checklists.restore');
        Route::resource('pm-checklists', AdminPreventiveMaintenanceChecklistController::class);

        // Messenger API Endpoints (Admin)
        Route::get('/api/messages/request/{id}', [\App\Http\Controllers\MessageController::class, 'getMessages']);
        Route::get('/api/messages/request/{id}/new', [\App\Http\Controllers\MessageController::class, 'getNewMessages']);
        Route::post('/api/messages/send', [\App\Http\Controllers\MessageController::class, 'sendMessage'])->middleware('throttle:messages');
        Route::post('/api/messages/{id}/read', [\App\Http\Controllers\MessageController::class, 'markAsRead']);
        Route::get('/api/messages/request/{id}/unread', [\App\Http\Controllers\MessageController::class, 'getUnreadCount']);
    });

require __DIR__ . '/auth.php';
