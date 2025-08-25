<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        $search = $request->input('search');
        if ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%");

        }
        $users = $query->latest()->paginate(10);
        return success($users, 'Users fetched successfully');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'is_admin' => 'boolean'
        ]);

        $data['password'] = Hash::make($data['password']);

        try {
            $user = User::create($data);
            return success($user, 'User created successfully', 201);
        } catch (\Exception $e) {
            return error('Failed to create user', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $user = User::with([
            'wallet:id,user_id,balance,total_earnings,total_withdrawals',
            'referrer:id,name,email',
            'loginLogs:id,user_id,ip,logged_in_at'
        ])
            ->select([
                'id',
                'name',
                'email',
                'username',
                'phone',
                'country',
                'country_code',
                'referral_code',
                'referred_by',
                'is_admin',
                'is_blocked',
                'kyc_status',
                'created_at'
            ])
            ->findOrFail($id);

        // Add commission by level manually
        $user->commission_by_level = $user->referralCommissionByLevel();

        return success($user, 'User fetched successfully');
    }


    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'is_admin' => 'boolean'
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        try {
            $user->update($data);
            return success($user, 'User updated successfully');
        } catch (\Exception $e) {
            return error('Failed to update user', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function destroy(User $user)
    {
        try {
            // Remove referral links
            User::where('referred_by', $user->id)->update(['referred_by' => null]);

            $user->delete();

            return success([], 'User deleted successfully');
        } catch (\Exception $e) {
            return error('Failed to delete user', 500, ['exception' => $e->getMessage()]);
        }
    }

    public function impersonate(User $user)
    {
        $token = $user->createToken('impersonation')->plainTextToken;

        return response()->json([
            'message' => 'Impersonation started',
            'token' => $token,
        ]);
    }

    public function adjustWallet(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|not_in:0',
        ]);

        $amount = $data['amount'];
        $wallet = $user->wallet;

        try {
            if ($amount > 0) {
                $wallet->deposit($amount);
            } else {
                $wallet->withdraw(abs($amount));
            }

            $user->transactions()->create([
                'amount' => $amount,
                'type' => 'direct_reward',
                'status' => 'completed',
                'description' => 'Manual wallet adjustment by admin',
            ]);

            return success(
                $wallet->only(['balance', 'total_earnings', 'total_withdrawals']),
                'Wallet adjusted successfully.'
            );
        } catch (\Exception $e) {
            return error($e->getMessage(), 422);
        }
    }

    public function toggleBlock(User $user)
    {
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return success(
            [],
            $user->is_blocked ? 'User blocked' : 'User unblocked',
        );
    }

}
