<?php

namespace App\Http\Controllers\Admin;

use App\Models\Withdrawal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\WithdrawalApproved;
use App\Http\Resources\WithdrawalResource;

class AdminWithdrawalController extends Controller
{
    public function index(Request $request)
    {
        $query = Withdrawal::with('user')->latest();

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search filter (by user name or email)
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Amount range filter
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        // Pagination
        $withdrawals = $query->paginate($request->get('page_size', 10));

        return success([
            'data' => WithdrawalResource::collection($withdrawals),
            'current_page' => $withdrawals->currentPage(),
            'last_page' => $withdrawals->lastPage(),
            'from' => $withdrawals->firstItem(),
            'to' => $withdrawals->lastItem(),
            'total' => $withdrawals->total(),
        ]);
    }


    public function approveWithdraw(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'processing') {
            return error('Only pending withdrawals can be approved', 400);
        }

        $withdrawal->status = 'completed';
        $withdrawal->save();


        $withdrawal->transaction()->update(['status' => 'completed']);

        // ✅ Notify the user
        $withdrawal->user->notify(new WithdrawalApproved($withdrawal));

        return success([], 'Withdrawal marked as completed and user notified');
    }
}
