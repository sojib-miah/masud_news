<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureReporterApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('user.login')
                ->with('modal', [
                    'type'    => 'error',
                    'title'   => 'লগইন প্রয়োজন',
                    'message' => 'অনুগ্রহ করে আগে লগইন করুন।',
                ]);
        }

        $isReporter = ($user->role ?? null) === 'reporter';
        $isApproved = ($user->status ?? null) === 'approved';

        if (! $isReporter || ! $isApproved) {
            return redirect()
                ->route('dashboard.reporter')
                ->with('modal', [
                    'type'    => 'error',
                    'title'   => 'অনুমোদন প্রয়োজন',
                    'message' => 'আপনি এখনো অনুমোদিত (approved) নন। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন। 01320991356',
                ]);
        }

        return $next($request);
    }
}
