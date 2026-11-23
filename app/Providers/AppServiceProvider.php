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
                return News::query()
                    ->select([
                        'id',
                        'title',
                        'slug',
                        'image',
                        'created_at',
                    ])
                    ->where('status', 1)
                    ->where('breaking', 1)
                    ->latest('created_at')
                    ->limit(10)
                    ->get();
            },

            'sidebarLatest' => function () {
                return News::query()
                    ->select([
                        'id',
                        'title',
                        'slug',
                        'image',
                        'created_at',
                    ])
                    ->where('status', 1)
                    ->latest('created_at')
                    ->limit(10)
                    ->get();
            },

            'sidebarPopular' => function () {
                return News::query()
                    ->select([
                        'id',
                        'title',
                        'slug',
                        'image',
                        'total_view',
                        'created_at',
                    ])
                    ->where('status', 1)
                    ->orderByDesc('total_view')
                    ->limit(10)
                    ->get();
            },
        ]);
    }
}
