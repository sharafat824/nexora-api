<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
   public function index()
{
    $data = [
        'total_users' => User::count(),
        'total_deposits' => Deposit::where('status', 'completed')->sum('amount'),
        'pending_deposits' => Deposit::where('status', 'pending')->count(),
        'total_withdrawals' => Withdrawal::where('status', 'completed')->sum('amount'),
        'pending_withdrawals' => Withdrawal::where('status', 'pending')->count(),

        // Recent activities
        'recent_users' => User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']),
        'recent_deposits' => Deposit::latest()->take(5)->get(['id', 'amount', 'status', 'created_at']),
        'recent_withdrawals' => Withdrawal::latest()->take(5)->get(['id', 'amount', 'status', 'created_at']),
        'unread_messages' => ChatMessage::where('direction', 'out')->where('read', false)->count(),

    ];

    return success($data, "success");
}

}
