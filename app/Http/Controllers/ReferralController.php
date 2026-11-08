<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReferralController extends Controller
{
    public function index(Request $request)
    {
        // 10 levels deep
        $depth = 10;

        // Build: referrals.referrals.referrals...
        $with = implode('.', array_fill(0, $depth, 'referrals'));

        $query = User::whereNull('referred_by')->with($with);

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Admin/Referral/Tree', [
            'users'          => $users,
            'search'         => $request->search,
            'totalUsers'     => User::count(),
            'totalRoots'     => User::whereNull('referred_by')->count(),
            'totalReferrals' => User::whereNotNull('referred_by')->count(),
        ]);
    }
}
