<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\ChatMessage;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get today's profit paid (assuming type is 'daily_income')
        $todayProfit = Transaction::where('type', 'daily_income')
            ->whereDate('created_at', today())
            ->sum('amount');

        // Active investments
        //  $activeInvestments = Investment::where('status', 'active')->count(); // Adjust based on your schema

        // Top 5 Referrers with levels
        $topReferrers = User::withCount('referrals')
            ->orderByDesc('referrals_count')
            ->take(5)
            ->get() // This returns full Eloquent model instances
            ->map(function (User $user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'referrals_count' => $user->referrals_count,
                    'levels' => $user->referralCommissionByLevel(), // Safe here
                ];
            });


        $data = [
            'total_users' => User::count(),
            'total_deposits' => Deposit::where('status', 'completed')->sum('amount'),
            'pending_deposits' => Deposit::where('status', 'pending')->count(),
            'total_withdrawals' => Withdrawal::where('status', 'completed')->sum('amount'),
            'pending_withdrawals' => Withdrawal::where('status', 'processing')->count(),

            'today_profit' => $todayProfit,
            //    'active_investments' => $activeInvestments,
            'top_referrers' => $topReferrers,

            'recent_users' => User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']),
            'recent_deposits' => Deposit::latest()->take(5)->get(['id', 'amount', 'status', 'created_at']),
            'recent_withdrawals' => Withdrawal::latest()->take(5)->get(['id', 'amount', 'status', 'created_at']),
            'unread_messages' => ChatMessage::where('direction', 'out')->where('read', false)->count(),
        ];

        return success($data, "success");
    }


}
