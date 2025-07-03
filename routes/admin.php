<?php

use App\Http\Middleware\IsAdmin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDepositController;
use App\Http\Controllers\Admin\AdminDashboardController;
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
    Route::put('/deposits/{deposit}/status', [AdminDepositController::class, 'approveDeposit']);
    Route::get('/withdrawals', [AdminWithdrawalController::class, 'index']);
    Route::put('/withdrawals/{withdrawal}/status', [AdminWithdrawalController::class, 'approveWithdraw']);

    // More admin routes...
});
