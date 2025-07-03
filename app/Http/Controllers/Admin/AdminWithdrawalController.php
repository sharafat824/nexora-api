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

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        if ($request->has('search') && $request->search !== '') {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

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
