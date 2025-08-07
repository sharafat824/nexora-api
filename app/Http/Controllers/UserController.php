<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        $user = $request->user()->load(['wallet', 'referrer']);

        return new UserResource($user);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'withdrawal_password' => 'nullable|string|min:8',
            'withdrawal_address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
            'country' => 'nullable|string|max:100',
            'country_code' => 'nullable|string|max:5',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        if ($request->has('withdrawal_password') && $request->withdrawal_password !== "*****************") {
            $user->withdrawal_password = Hash::make($request->withdrawal_password);
        }
        if ($request->has('withdrawal_address')) {
            $user->withdrawal_address = $request->withdrawal_address;
        }
        if ($request->hasFile('avatar')) {
            $uploadsPath = public_path('uploads/avatars');

            // Create the folder if it doesn't exist
            if (!File::exists($uploadsPath)) {
                File::makeDirectory($uploadsPath, 0755, true); // recursive = true
            }

            // Store the file
            $path = $request->file('avatar')->store('avatars', 'uploads');
            $user->avatar = $path;
        }
        if ($request->has('country')) {
            $user->country = $request->country;
        }

        if ($request->has('country_code')) {
            $user->country_code = $request->country_code;
        }

        $user->save();

        return new UserResource($user);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required_with:new_password|same:new_password',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return success([], 'Password changed successfully');
    }

    public function index(Request $request)
    {
        $user = $request->user()->load(['wallet', 'activeInvestment.plan']);

        $announcement = Announcement::where('active', true)->latest()->first();
        $teamCount = isset($user->team) ? count($user->team) : 0;
        $commissions = $user->referralCommissionByLevel();

        $allEarnings = $user->dailyEarnings();
        $today = now()->toDateString();
        $todayEarnings = $user->dailyEarnings($today, $today);
        $weekEarnings = $user->dailyEarnings(now()->subDays(7)->toDateString(), now()->toDateString());

        $totalEarnings = $allEarnings->sum('amount');
        $todayTotal = $todayEarnings->sum('amount');
        $weekTotal = $weekEarnings->sum('amount');

        $activeInvestment = $user->activeInvestment()->with('plan')->first();
        $hasActiveInvestment = (bool) $activeInvestment;

        $canCollectDailyIncome = true;

        if ($hasActiveInvestment) {
            // Check if already collected today
            $alreadyCollected = $user->transactions()
                ->where('type', 'daily_income')
                ->whereDate('created_at', $today)
                ->exists();

            // Check if plan was activated today
            $activatedToday = $activeInvestment->start_date->isSameDay($today);

            // Can only collect if:
            // 1. Not collected already
            // 2. Plan was NOT started today
            $canCollectDailyIncome = !$alreadyCollected && !$activatedToday;
        }


        $activePlanInfo = null;
        if ($activeInvestment && $activeInvestment->plan) {
            $activePlanInfo = [
                'name' => $activeInvestment->plan->name,
                'end_date' => $activeInvestment->end_date->toDateString(),
            ];
        }

        $totalInvestedWithReferrals = $user->totalInvestment();
        $referralCommissionEarnings = $user->referralEarnings()
            ->where('type', 'team_reward')
            ->sum('amount');
        $userInvestment = $user->deposits()->completed()->sum('amount');


        return (new UserResource($user))->additional([
            'team_count' => $teamCount,
            'anouncement' => $announcement,
            'referral_earning' => $commissions,
            'has_active_investment' => $hasActiveInvestment,
            'can_collect_daily_income' => $canCollectDailyIncome,
            'active_plan' => $activePlanInfo,
            'total_investment' => $totalInvestedWithReferrals,
            'user_investment' => $userInvestment,
            'team_reward' => $referralCommissionEarnings,
            'daily_earnings' => [
                'total' => $totalEarnings,
                'today' => $todayTotal,
                'last_7_days' => $weekTotal,
                'history' => $allEarnings,
            ],
        ]);
    }



    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:2048',
        ]);

        $user = $request->user();

        // Delete old avatar if it exists
        if ($user->avatar && Storage::disk('uploads')->exists($user->avatar)) {
            Storage::disk('uploads')->delete($user->avatar);
        }

        // Ensure uploads/avatars folder exists
        $uploadsPath = base_path('uploads/avatars');
        if (!File::exists($uploadsPath)) {
            File::makeDirectory($uploadsPath, 0755, true);
        }

        // Store new avatar to uploads/avatars
        $path = $request->file('avatar')->store('avatars', 'uploads');
        $user->avatar = $path;
        $user->save();

        return response()->json([
            'avatar' => asset('uploads/' . $user->avatar) // ✅ Correct URL
        ]);
    }

    public function announcement()
    {
        $announcement = Announcement::where('active', true)->latest()->first();
        return success($announcement);
    }

}


