<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('dang-ky', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('dang-ky', [RegisteredUserController::class, 'store']);

    Route::get('dang-nhap', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('dang-nhap', [AuthenticatedSessionController::class, 'store']);

    Route::get('quen-mat-khau', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('quen-mat-khau', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('dat-lai-mat-khau/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('dat-lai-mat-khau', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('xac-thuc-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('xac-thuc-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('xac-thuc-email/gui-lai', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('xac-nhan-mat-khau', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('xac-nhan-mat-khau', [ConfirmablePasswordController::class, 'store']);

    Route::put('tai-khoan/mat-khau', [PasswordController::class, 'update'])->name('password.update');

    Route::post('dang-xuat', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
