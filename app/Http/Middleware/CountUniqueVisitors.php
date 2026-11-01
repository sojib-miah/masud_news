<?php

namespace App\Http\Middleware;

use App\Models\SiteVisitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CountUniqueVisitors
{
    public function handle(Request $request, Closure $next): Response
    {
        // Count public page visits, not admin/API requests.
        $shouldCount =
            $request->isMethod('GET')
            && ! $request->is('admin', 'admin/*')
            && ! $request->is('api/*')
            && ! $request->expectsJson();

        $visitorId = $request->cookie('site_visitor_id');

        if ($shouldCount && ! $visitorId) {
            $visitorId = (string) Str::uuid();

            SiteVisitor::firstOrCreate([
                'visitor_id' => $visitorId,
            ]);
        }

        $response = $next($request);

        if ($shouldCount && ! $request->cookie('site_visitor_id')) {
            $response->withCookie(cookie(
                'site_visitor_id',
                $visitorId,
                60 * 24 * 365, // 1 year
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax'
            ));
        }

        return $response;
    }
}
