<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Models\PlatformSetting;
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
         $minDeposit = PlatformSetting::getValue('min_deposit', 20);

        $request->validate([
            'amount' => ['required', 'numeric', 'min:' . $minDeposit],
            'payment_method' => 'required|string|in:bank,card,crypto',
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ], [
            'amount.min' => "Minimum deposit amount is $minDeposit."
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
}
