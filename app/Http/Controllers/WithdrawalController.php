<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlatformSetting;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\WithdrawalResource;

class WithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $withdrawals = $request->user()->withdrawals()
            ->latest()
            ->paginate(15);

        return WithdrawalResource::collection($withdrawals);
    }

    public function store(Request $request)
    {
        $minWithdraw = PlatformSetting::getValue('min_withdraw', 5); // fallback default if not found

        $request->validate([
            'amount' => 'required|numeric|min:' . $minWithdraw,
            'wallet_address' => 'required|string',
            'otp' => 'required|string',
            'password' => 'required|string'
        ], [
            'amount.min' => "Minimum withdrawal amount is $minWithdraw."
        ]);
        $user = $request->user();

        if (!Hash::check($request->password, $user->withdrawal_password)) {
            return response()->json([
                'message' => 'Invalid withdrawal password'
            ], 422);
        }

        $cachedOtp = Cache::get('withdrawal_otp_' . $user->id);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json([
                'message' => 'Invalid or expired OTP'
            ], 422);
        }

        // OTP is valid, remove it to prevent reuse
        Cache::forget('withdrawal_otp_' . $user->id);

        $wallet = $user->wallet;

        // Calculate fee (5%)
        $feePercentage = (float) PlatformSetting::getValue('withdraw_fee', 5); // default fallback 5%
        $fee = $request->amount * ($feePercentage / 100);
        $total = $request->amount + $fee;


        if ($wallet->balance < $total) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Deduct from balance
            $wallet->withdraw($total);
            $wallet->recordWithdrawal($request->amount);

            // Create withdrawal record
            $withdrawal = $user->withdrawals()->create([
                'amount' => $request->amount,
                'fee' => $fee,
                'wallet_address' => $request->wallet_address,
                'method' => 'crypto', // assuming crypto
                'status' => 'processing'
            ]);

            // Record transaction
            $user->transactions()->create([
                'amount' => $total,
                'type' => 'withdrawal',
                'status' => 'processing',
                'reference_id' => $withdrawal->id
            ]);

            DB::commit();

            return new WithdrawalResource($withdrawal);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while processing your withdrawal. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function sendOtp(Request $request)
    {
        $user = $request->user();

        $otp = rand(100000, 999999);

        // Store in cache for 5 mins tied to user id
        Cache::put('withdrawal_otp_' . $user->id, $otp, now()->addMinutes(5));

        // Send via email (simple)
        Mail::raw("Your withdrawal OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your Withdrawal OTP');
        });

        return response()->json(['message' => 'OTP sent to your email']);
    }

}
