<?php

namespace App\Http\Controllers\Admin;

use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Notifications\WithdrawalApproved;
use App\Notifications\WithdrawalRejected;
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

    public function updateStatus(Request $request, Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'processing') {
            return error('Only processing withdrawals can be updated', 400);
        }

        $validated = $request->validate([
            'status' => 'required|in:completed,rejected',
            'rejection_reason' => 'nullable|string|max:255',
            // 'admin_notes' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $status = $validated['status'];

            $withdrawal->status = $status;
       //     $withdrawal->admin_notes = $validated['admin_notes'] ?? null;

            if ($status === 'rejected') {
                $withdrawal->rejection_reason = $validated['rejection_reason'] ?? 'Rejected by admin';

                // Refund the user (amount + fee)
                $wallet = $withdrawal->user->wallet;
                $refundAmount = $withdrawal->amount + $withdrawal->fee;
                $wallet->deposit($refundAmount, 'Refund for rejected withdrawal #' . $withdrawal->id);
            }

            $withdrawal->save();

            // Update linked transaction
            $withdrawal->transaction()->update(['status' => $status]);

            // Notify the user
            if ($status === 'completed') {
                $withdrawal->user->notify(new WithdrawalApproved($withdrawal));
            } else {
                $withdrawal->user->notify(new WithdrawalRejected($withdrawal));
            }

            DB::commit();

            return success([], "Withdrawal has been {$status}");
        } catch (\Exception $e) {
            DB::rollBack();
            return error('Failed to update withdrawal: ' . $e->getMessage(), 500);
        }
    }


}
