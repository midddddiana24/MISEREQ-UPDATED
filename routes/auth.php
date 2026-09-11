<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\AdminDeviceVerificationController;
use App\Http\Controllers\Auth\RequiredPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:5,1');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:10,1');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('admin-device/verify', [AdminDeviceVerificationController::class, 'show'])
        ->name('admin.device.verify');
    Route::post('admin-device/verify-code', [AdminDeviceVerificationController::class, 'verifyCode'])
        ->middleware('throttle:5,1')
        ->name('admin.device.verify-code');
    Route::post('admin-device/continue', [AdminDeviceVerificationController::class, 'continueApproved'])
        ->middleware('throttle:10,1')
        ->name('admin.device.continue');
    Route::get('admin-device/review/{publicId}', [AdminDeviceVerificationController::class, 'review'])
        ->name('admin.device.review');
    Route::post('admin-device/review/{publicId}/approve', [AdminDeviceVerificationController::class, 'approve'])
        ->middleware('throttle:sensitive')
        ->name('admin.device.approve');
    Route::post('admin-device/review/{publicId}/deny', [AdminDeviceVerificationController::class, 'deny'])
        ->middleware('throttle:sensitive')
        ->name('admin.device.deny');

    Route::get('password/required', [RequiredPasswordController::class, 'edit'])
        ->name('password.required');
    Route::put('password/required', [RequiredPasswordController::class, 'update'])
        ->middleware('throttle:sensitive')
        ->name('password.required.update');

    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware('throttle:6,1');

    Route::put('password', [PasswordController::class, 'update'])
        ->middleware('throttle:sensitive')
        ->name('password.update');

    Route::get('2fa/setup', [TwoFactorAuthController::class, 'showSetup'])
        ->name('2fa.setup');

    Route::post('2fa/setup', [TwoFactorAuthController::class, 'storeSetup'])
        ->middleware('throttle:5,1')
        ->name('2fa.setup.store');

    Route::get('2fa/verify', [TwoFactorAuthController::class, 'showVerify'])
        ->name('2fa.verify');

    Route::post('2fa/verify', [TwoFactorAuthController::class, 'verify'])
        ->middleware('throttle:5,1')
        ->name('2fa.verify.store');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
