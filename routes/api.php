<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/platform-status', function () {
    return response()->json([
        'maintenance_mode' => (bool) platform_setting('maintenance_mode') // use helper or model directly
    ]);
});

Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/verify/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify/otp', [AuthController::class, 'verifyOtp']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [UserController::class, 'index']);
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);

    // User routes
    Route::get('/user', [UserController::class, 'getUser']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    Route::put('/user/password', [UserController::class, 'changePassword']);

    // Wallet routes
    Route::get('/wallet', [WalletController::class, 'getWallet']);

    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index']);

    // Deposit routes
    Route::get('/deposits', [DepositController::class, 'index']);
    Route::post('/deposits', [DepositController::class, 'store']);

    // Withdrawal routes
    Route::get('/withdrawals', [WithdrawalController::class, 'index']);
    Route::post('/withdrawals', [WithdrawalController::class, 'store']);
    Route::post('/withdrawals/send-otp', [WithdrawalController::class, 'sendOtp']);


    // Referral routes
    Route::get('/referrals', [ReferralController::class, 'index']);
    Route::get('/referrals/earnings', [ReferralController::class, 'earnings']);
    Route::get('/referrals/team-stats', [ReferralController::class, 'teamStats']);

    // Chat routes
    Route::get('/chat', [ChatController::class, 'index']);
    Route::post('/chat', [ChatController::class, 'store']);
    Route::post('/chat/mark-read', [ChatController::class, 'markAsRead']);
    Route::get('/chat/unread-count', function (Request $request) {
        $count = \App\Models\ChatMessage::where('user_id', $request->user()->id)
            ->where('direction', 'in') // admin → user
            ->where('read', false)
            ->count();

        return response()->json(['count' => $count]);
    });
    Route::get('earnings/daily', [TransactionController::class, 'dailyEarnings']);
    Route::post('/user/profile/avatar', [UserController::class, 'uploadAvatar']);
    Route::get('/announcement', [UserController::class, 'announcement']);
    Route::get('/countries', function () {
        return response()->json(config("countries"));
    });

    Route::get('/user/investment-plans', [\App\Http\Controllers\User\InvestmentController::class, 'plans']);
    Route::post('/user/investment-plans/activate', [\App\Http\Controllers\User\InvestmentController::class, 'activate']);
  Route::post('user/collect-daily-income', [\App\Http\Controllers\User\DailyIncomeController::class, 'collect']);

});

require __DIR__ . '/admin.php';
