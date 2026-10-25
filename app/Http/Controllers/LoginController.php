<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return Inertia::render('FrontEnd/Login/Login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required',
        ]);

        $credentials = ['email' => $request->name, 'password' => $request->password];
        if (Auth::attempt($credentials, $request->remember)) {
            return $this->redirectByRole();
        }

        $credentials = ['phone' => $request->name, 'password' => $request->password];
        if (Auth::attempt($credentials, $request->remember)) {
            return $this->redirectByRole();
        }

        return back()->withErrors([
            'name' => 'Invalid credentials',
        ]);
    }

    protected function redirectByRole()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'reporter') {
            return redirect()->route('dashboard.reporter');
        }

        if ($user->role === 'pathock') {
            return redirect()->route('dashboard.pathock');
        }

        if ($user->role === 'motamot') {
            return redirect()->route('dashboard.motamot');
        }
        return redirect()->route('home');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('user.login');
    }

    public function forgotPassword()
    {
        return Inertia::render('FrontEnd/Login/ForgotPassword');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['name' => 'required']);

        $input = $request->name;

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $status = Password::sendResetLink(['email' => $input]);

            return $status === Password::RESET_LINK_SENT
                ? back()->with('status', __($status))
                : back()->withErrors(['name' => __($status)]);
        } else {
            $user = User::where('phone', $input)->first();
            if (!$user) {
                return back()->withErrors(['name' => 'Phone not found']);
            }

            $otp = rand(100000, 999999);
            DB::table('password_resets')->updateOrInsert(
                ['phone' => $user->phone],
                ['token' => $otp, 'created_at' => now()]
            );

            return back()->with('status', 'OTP sent to your phone');
        }
    }

    public function resetPassword($token)
    {
        return Inertia::render('FrontEnd/Login/ResetPassword', ['token' => $token]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'password' => 'required|confirmed|min:6',
            'token' => 'required',
        ]);

        $input = $request->name;

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->password = bcrypt($password);
                    $user->save();
                }
            );

            return $status === Password::PASSWORD_RESET
                ? redirect()->route('user.login')->with('status', __($status))
                : back()->withErrors(['name' => [__($status)]]);
        } else {
            $record = DB::table('password_resets')
                ->where('phone', $input)
                ->where('token', $request->token)
                ->first();

            if (!$record) {
                return back()->withErrors(['name' => 'Invalid token']);
            }

            $user = User::where('phone', $input)->first();
            $user->password = bcrypt($request->password);
            $user->save();

            DB::table('password_resets')->where('phone', $input)->delete();

            return redirect()->route('user.login')->with('status', 'Password reset successful');
        }
    }
}
