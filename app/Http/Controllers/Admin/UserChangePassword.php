<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserChangePassword extends Controller
{
    /**
     * Show the users list page.
     */
    public function index()
    {
        return Inertia::render('Admin/UserPassword/UserPassword');
    }

    /**
     * Return users (JSON) with optional search filter.
     * GET /admin/users/list?search=...
     */
    public function list(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->select('id', 'name', 'phone', 'email', 'role', 'created_at')
            ->orderByDesc('id')
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * Update a user's password (admin only — no old password required).
     * POST /admin/users/{user}/change-password
     */
    public function changePassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে।',
        ]);
    }
}
