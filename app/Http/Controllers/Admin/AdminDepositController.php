<?php

namespace App\Http\Controllers\Admin;

use App\Models\Deposit;
use Illuminate\Http\Request;
use App\Services\ReferralService;
use App\Http\Controllers\Controller;
use App\Http\Resources\DepositResource;

class AdminDepositController extends Controller
{
    protected $referralService;
    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
    }

    public function index(Request $request)
    {
        $query = Deposit::with('user')->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user name or email
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $deposits = $query->paginate($request->get('page_size', 10));

        return success([
            'data' => DepositResource::collection($deposits),
            'current_page' => $deposits->currentPage(),
            'last_page' => $deposits->lastPage(),
            'from' => $deposits->firstItem(),
            'to' => $deposits->lastItem(),
            'total' => $deposits->total(),
        ], 'Deposits fetched');
    }


    public function approveDeposit(Deposit $deposit)
    {

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

        if (!$deposit->user->is_commission_distributed) {
            $this->referralService->distributeCommissions($deposit->user, $deposit->amount);
            $deposit->user->is_commission_distributed = true;
            $deposit->user->save();
        }

        return response()->json([
            'message' => 'Deposit approved successfully',
            'deposit' => new DepositResource($deposit)
        ]);
    }
}
