<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
        $request->validate([
            'amount' => 'required|numeric|min:20',
            'wallet_address' => 'required|string',
            'method' => 'required|string|in:bank,crypto'
        ]);

        $user = $request->user();
        $wallet = $user->wallet;

        // Calculate fee (5%)
        $fee = $request->amount * 0.05;
        $total = $request->amount + $fee;

        if ($wallet->balance < $total) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 400);
        }

        // Deduct from balance
        $wallet->withdraw($total);
        $wallet->recordWithdrawal($request->amount);

        // Create withdrawal
        $withdrawal = $user->withdrawals()->create([
            'amount' => $request->amount,
            'fee' => $fee,
            'wallet_address' => $request->wallet_address,
            'method' => $request->method,
            'status' => 'processing'
        ]);

        // Record transaction
        $user->transactions()->create([
            'amount' => -$total,
            'type' => 'withdrawal',
            'status' => 'processing',
            'reference_id' => $withdrawal->id
        ]);

        // In a real app, you would process the withdrawal here
        // For now, we'll simulate admin processing

        return new WithdrawalResource($withdrawal);
    }
}
