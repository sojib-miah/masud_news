<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Footer;
use App\Models\Header;
use App\Models\News;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Inertia::share([
            'header' => fn() => Header::first(),
            'headers' => fn() => Header::all(),
            'footer' => fn() => Footer::first(),
            'categories' => function () {
                return Category::with('subCategories')->get();
            },
            'breakingNews' => function () {
                return News::where('breaking', 1)
                    ->latest()
                    ->take(10)
                    ->get();
            },
            'sidebarLatest' => function () {
                return News::where('status', 1)->latest()
                    ->take(20)
                    ->get();
            },

            'sidebarPopular' => function () {
                return News::where('status', 1)->orderByDesc('total_view')
                    ->take(20)
                    ->get();
            }
        ]);
    }
}
