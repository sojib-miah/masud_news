<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferralController extends Controller
{
    public function index()
    {
        $users = User::whereNull('referred_by')
            ->with('referrals.referrals.referrals.referrals.referrals')
            ->latest()->paginate(10);

        return Inertia::render('Admin/Referral/Tree', [
            'users' => $users
        ]);
    }
}
