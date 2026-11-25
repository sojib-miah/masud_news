<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ImpersonateController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Impersonation/Index');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_field' => 'required|string',
        ]);

        $loginField = trim($request->login_field);
        $user = User::where('id', $loginField)
            ->orWhere('phone', $loginField)
            ->first();

        if (!$user) {
            return back()->withErrors(['login_field' => 'এই আইডি বা ফোন নাম্বার দিয়ে কোনো ইউজার পাওয়া যায়নি!']);
        }
        if (auth()->id() === $user->id) {
            return back()->withErrors(['login_field' => 'আপনি ইতিমধ্যে এই ইউজার হিসেবে লগইন আছেন!']);
        }
        $adminId = Auth::id();
        session()->put('impersonator_id', $adminId);
        Auth::login($user);
        return $this->redirectBasedOnRole($user);
    }

    public function leave()
    {
        if (!session()->has('impersonator_id')) {
            return back()->withErrors(['error' => 'আপনি বর্তমানে ইম্পারসোনেট করছেন না!']);
        }

        $adminId = session()->pull('impersonator_id');
        Auth::loginUsingId($adminId);

        return redirect()->route('admin.dashboard')->with('status', 'সফলভাবে অ্যাডমিন প্যানেলে ফিরে এসেছেন!');
    }

    private function redirectBasedOnRole($user)
    {
        if ($user->role === 'reporter') {
            return redirect()->route('dashboard.reporter');
        }
        if ($user->role === 'pathock') {
            return redirect()->route('dashboard.pathock');
        }
        if ($user->role === 'motamot') {
            return redirect()->route('dashboard.motamot');
        }

        return redirect()->back();
    }
}
