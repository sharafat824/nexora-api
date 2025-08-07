<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReferralEarning;
use App\Http\Resources\UserResource;
use App\Http\Resources\DepositResource;
use App\Http\Resources\WithdrawalResource;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->perPage ?? 10;
        $type = $request->type;

        switch ($type) {
            case 'deposit':
                $transactions = $request->user()->deposits()->latest()->paginate($perPage);
                return DepositResource::collection($transactions);

            case 'withdraw':
                $transactions = $request->user()->withdrawals()->latest()->paginate($perPage);
                return WithdrawalResource::collection($transactions);

            default:
                $transactions = $request->user()->transactions()->latest()->paginate($perPage);
                return TransactionResource::collection($transactions);
        }
    }
    public function dailyEarnings(Request $request)
    {
        $user = $request->user();

        $perPage = $request->get('per_page', 20);

        $transactions = $user->transactions()
            ->where('type', 'daily_income')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return TransactionResource::collection($transactions)->additional(["user" => ['name' => $user->name, 'email' => $user->email]]);
    }

    public function teamRewards(Request $request)
    {
        $user = $request->user();

        $perPage = $request->get('per_page', 20);

        $transactions = $user->transactions()
            ->where('type', 'team_reward')
            ->orderByDesc('created_at')
            ->with(['user', 'referenceUser']) // 👈 include reference user
            ->paginate($perPage);

        return TransactionResource::collection($transactions)->additional([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

}
