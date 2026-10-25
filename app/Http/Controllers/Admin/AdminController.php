<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\ReporterType;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Laravolt\Avatar\Facade as Avatar;

class AdminController extends Controller
{
    use AuthorizesRequests;

    public function adminDashboard()
    {
        $this->authorize('dashboard.show');

        // NEWS
        $todayNews = News::whereDate('created_at', today())->count();

        $monthNews = News::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $totalNews = News::count();

        // SALES
        $todaySell = Order::whereDate('created_at', today())
            ->sum('total');

        $monthSell = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $totalSell = Order::sum('total');

        // SALES CHART
        $monthlySales = Order::select(
            DB::raw("MONTH(created_at) as month"),
            DB::raw("SUM(total) as total")
        )
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [];
        $sales = [];

        for ($i = 1; $i <= 12; $i++) {

            $months[] = date('M', mktime(0, 0, 0, $i, 1));

            $found = $monthlySales->firstWhere('month', $i);

            $sales[] = $found ? $found->total : 0;
        }

        return Inertia::render('Admin/Index', [
            'todayNews' => $todayNews,
            'monthNews' => $monthNews,
            'totalNews' => $totalNews,

            // sales
            'todaySell' => (float) $todaySell,
            'monthSell' => (float) $monthSell,
            'totalSell' => (float) $totalSell,
            'months' => $months,
            'sales' => $sales,
        ]);
    }

    // push notification
    public function notifications()
    {
        $user = auth()->user();
        $user->unreadNotifications->markAsRead();

        return inertia('Admin/Notifications/NotificationsPage', [
            'notifications' => $user->notifications()->latest()->get()
        ]);
    }

    public function subscriptions(Request $request)
    {
        $query = NewsletterSubscriber::query();

        if ($request->search) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        $subscriptions = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Subscriptions/Subscriptions', [
            'subscriptions' => $subscriptions,
            'search' => $request->search,
        ]);
    }

    public function destroy($id)
    {
        NewsletterSubscriber::findOrFail($id)->delete();
        return redirect()->route('subscriptions')->with('status', 'Subscription deleted successfully!');
    }

    // pathock reporter 
    public function pathock(Request $request)
    {
        $query = User::where('role', 'pathock');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('Admin/Pathock/Pathock', [
            'pathocks' => $query->latest()->paginate(10)->withQueryString(),
            'search' => $request->search
        ]);
    }

    public function reporter(Request $request)
    {
        $reporters = User::where('role', 'reporter')
            ->with([
                'reporterProfile.divisionData',
                'reporterProfile.districtData',
                'reporterProfile.upazilaData',
                'reporterProfile.unionData'
            ])
            ->latest()
            ->paginate(10);

        $types = ReporterType::pluck('name', 'id'); // [id => name]

        // map types
        $reporters->getCollection()->transform(function ($user) use ($types) {
            if ($user->reporterProfile && $user->reporterProfile->reporter_types) {

                $ids = explode(',', $user->reporterProfile->reporter_types);

                $names = collect($ids)
                    ->map(fn($id) => $types[$id] ?? null)
                    ->filter()
                    ->values();

                $user->reporter_type_names = $names;
            } else {
                $user->reporter_type_names = [];
            }

            return $user;
        });

        return Inertia::render('Admin/Reporter/Reporter', [
            'reporters' => $reporters
        ]);
    }

    public function pathockDestroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return back()->with('status', 'User deleted successfully!');
    }
}
