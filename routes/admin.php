<?php

use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDepositController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\InvestmentPlanController;
use App\Http\Controllers\Admin\AdminWithdrawalController;

Route::middleware(['auth:sanctum', IsAdmin::class])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);

    // Manage users
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::post('/users', [AdminUserController::class, 'store']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::put('/users/{user}', [AdminUserController::class, 'update']);
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

    // // Withdrawals
    // Route::get('/withdrawals', [AdminWithdrawalController::class, 'index']);
    // Route::put('/withdrawals/{withdrawal}/status', [AdminWithdrawalController::class, 'updateStatus']);

    // // Deposits
    Route::get('/deposits', [AdminDepositController::class, 'index']);
    Route::put('/deposits/{deposit}/status', [AdminDepositController::class, 'updateDepositStatus']);
    Route::get('/deposits/export', [AdminDepositController::class, 'export']);
    Route::post('/deposits', [AdminDepositController::class, 'store']);

    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index']);
    Route::put('/withdrawals/{withdrawal}/status', [AdminWithdrawalController::class, 'updateStatus']);

    Route::post('/users/{user}/impersonate', [AdminUserController::class, 'impersonate']);

    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::post('/general', [SettingController::class, 'updateGeneral']);
        Route::post('/commissions', [SettingController::class, 'updateCommissions']);
        Route::post('/upload-logo', [SettingController::class, 'uploadLogo']);
    });

    Route::get('/chat/users', [AdminChatController::class, 'getChatUsers']);
    Route::get('/chat/messages/{user}', [AdminChatController::class, 'getMessages']);
    Route::post('/chat/messages/{user}', [AdminChatController::class, 'sendMessage']);
    Route::post('/chat/mark-read/{user}', [AdminChatController::class, 'markAsRead']);

    Route::get('/announcement', [AdminSettingController::class, 'getAnnouncement']);
    Route::post('/announcement', [AdminSettingController::class, 'saveAnnouncement']);
    Route::post('/users/{user}/wallet-adjust', [AdminUserController::class, 'adjustWallet']);
    Route::post('users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock']);

    Route::get('investment-plans', [InvestmentPlanController::class, 'index']);
    Route::post('investment-plans', [InvestmentPlanController::class, 'update']);

    // More admin routes...
});
