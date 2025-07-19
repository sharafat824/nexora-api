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

    public function show(User $user)
    {
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
}
