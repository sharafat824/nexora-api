<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Services\ReferralService;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepositResource;

class DepositController extends Controller
{
       protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index(Request $request)
    {
        $deposits = $request->user()->deposits()
            ->latest()
            ->paginate(15);

        return DepositResource::collection($deposits);
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:20',
            'payment_method' => 'required|string|in:bank,card,crypto',
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $user = $request->user();

        // Store proof file
        $proofPath = $request->file('proof')->store('deposit_proofs', 'public');

        // Create deposit record
        $deposit = $user->deposits()->create([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
            'proof' => $proofPath
        ]);

        // In a real app, you would integrate with a payment gateway here
        // For now, we'll simulate admin approval

        return new DepositResource($deposit);
    }

    public function approveDeposit(Deposit $deposit)
    {
        // This would typically be in an AdminController
        // Included here for completeness

        if ($deposit->status !== 'pending') {
            return response()->json([
                'message' => 'Deposit already processed'
            ], 400);
        }

        $deposit->status = 'completed';
        $deposit->save();

        // Credit user's wallet
        $deposit->user->wallet->deposit($deposit->amount);

        // Record transaction
        $deposit->user->transactions()->create([
            'amount' => $deposit->amount,
            'type' => 'deposit',
            'status' => 'completed',
            'reference_id' => $deposit->id
        ]);

        // Pay referral commissions
        $this->referralService->distributeCommissions($deposit->user, $deposit->amount);

        return response()->json([
            'message' => 'Deposit approved successfully',
            'deposit' => new DepositResource($deposit)
        ]);
    }
}
