<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FrontEnd\TermesController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MotamotController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PathockController;
use App\Http\Controllers\ProductDetailsController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReporterController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockMoneyController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

// include route
require __DIR__.'/admin.php';
require __DIR__.'/user.php';

// all route here
Route::get('/', [UserController::class, 'home'])->name('home');

// terms & condition
Route::get('news/termes&conditions', [TermesController::class, 'newsTermes'])->name('news.termes');
Route::get('news/privacyPolicy', [TermesController::class, 'newsPrivacy'])->name('news.privacy');

Route::get('/register', [RegisterController::class, 'register'])->name('register');
Route::get('/register/pathock', [RegisterController::class, 'pathockRegister'])->name('pathock.register');
Route::get('/register/reporter', [RegisterController::class, 'reporterRegister'])->name('reporter.register');
Route::get('/register/motamot', [RegisterController::class, 'motamotRegister'])->name('motamot.register');

Route::post('/register/pathock', [RegisterController::class, 'storePathock'])->name('pathock.store');
Route::post('/register/reporter', [RegisterController::class, 'storeReporter'])->name('reporter.store');
Route::post('/register/motamot', [RegisterController::class, 'storeMotamot'])->name('motamot.store');

Route::get('/districts/{divisionId}', [RegisterController::class, 'getDistricts']);
Route::get('/upazilas/{districtId}', [RegisterController::class, 'getUpazilas']);
Route::get('/unions/{upazilaId}', [RegisterController::class, 'getUnions']);
Route::get('/city-corporations/{divisionId}', [RegisterController::class, 'getCityCorporations']);
Route::get('/wards/{cityCorporationId}', [RegisterController::class, 'getWards']);

// login
Route::post('/logout', [LoginController::class, 'logout'])->name('user.logout');
Route::get('/login', [LoginController::class, 'login'])->name('user.login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.store');

// Forgot password
Route::get('/forgot-password', [LoginController::class, 'forgotPassword'])->name('password.request');
Route::post('/forgot-password', [LoginController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [LoginController::class, 'resetPassword'])->name('password.reset');
Route::post('/reset-password', [LoginController::class, 'updatePassword'])->name('password.update');

// news
Route::get('/news/details/{slug}', [UserController::class, 'newsDetails'])->name('news.details');
Route::get('/news/archive', [UserController::class, 'newsArchive'])->name('news.archive');
Route::get('/news/{category}/{subcategory?}', [UserController::class, 'allNews'])->name('all.news');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/pathock', [UserController::class, 'pathockDashboard'])->name('dashboard.pathock');
    Route::get('/dashboard/reporter', [UserController::class, 'reporterDashboard'])->name('dashboard.reporter');
    Route::get('/dashboard/motamot', [UserController::class, 'motamotDashboard'])->name('dashboard.motamot');
    Route::get('/pathock/profile', [UserController::class, 'pathockProfile'])->name('pathock.profile');
    Route::post('/pathock/profile/update', [UserController::class, 'pathockProfileUpdate'])->name('pathock.profile.update');
    Route::get('/pathock/payment', [UserController::class, 'pathockPayment'])->name('pathock.payment');
    Route::get('/pathock/refferals', [PathockController::class, 'pathockRefferals'])->name('pathock.refferals');
    Route::get('pathock/orders', [PathockController::class, 'pathockOrders'])->name('pathock.orders');

    Route::get('/pathock/order/invoice/{id}', [PathockController::class, 'invoice'])->name('order.invoice');
    Route::get('/pathock/order/invoice/{id}/pdf', [PathockController::class, 'invoicePdf'])->name('order.invoice.pdf');

    Route::resource('stock-money', StockMoneyController::class);

    Route::get('/reporter/{id}/edit', [UserController::class, 'editReporter'])->name('reporter.edit');

    Route::post('/reporter/{id}/update', [UserController::class, 'updateReporter'])->name('reporter.update');

    Route::get('/reporter/applicationform', [UserController::class, 'applicationForm'])->name('reporter.application');

    // motamot dashboard
    Route::get('/motamot/profile', [UserController::class, 'motamotProfile'])->name('motamot.profile');
    Route::post('/motamot/profile/update', [UserController::class, 'motamotProfileUpdate'])->name('motamot.profile.update');

    Route::get('/motamot', [MotamotController::class, 'index'])->name('motamot.index');
    Route::post('/motamot/store', [MotamotController::class, 'store'])->name('motamot.store');
    Route::put('/motamot/update/{id}', [MotamotController::class, 'update'])->name('motamot.update');
    Route::delete('/motamot/delete/{id}', [MotamotController::class, 'destroy'])->name('motamot.destroy');

    Route::get('/add/news', [UserController::class, 'addNews'])->name('add.news');
    Route::post('/count-news-view', [UserController::class, 'countView'])->name('news.count.view');

    Route::get('/idcard', [ReporterController::class, 'idCard'])->name('id.card');
    Route::get('/draftnews', [ReporterController::class, 'news'])->name('news');
    Route::get('/audio/news', [ReporterController::class, 'audioNews'])->name('audio.news');
    Route::get('/video/news', [ReporterController::class, 'videoNews'])->name('video.news');

    Route::post('/audio/store', [NewsController::class, 'audioStore'])->name('audio.store');
    Route::post('/video/store', [NewsController::class, 'videoStore'])->name('video.store');

    Route::resource('reporter/news', NewsController::class);

    Route::get('/all/News', [NewsController::class, 'allNews'])->name('news.all');
    Route::get('/featured/News', [NewsController::class, 'featuredNews'])->name('featured.news');
    Route::get('/breaking/News', [NewsController::class, 'breakingNews'])->name('breaking.news');
    Route::get('/pending/News', [NewsController::class, 'pendingNews'])->name('pending.news');
    Route::get('/schedule/News', [NewsController::class, 'scheduleNews'])->name('schedule.news');
    Route::get('/draft/News', [NewsController::class, 'draftNews'])->name('draft.news');

    Route::get('/all/reporter', [ReporterController::class, 'allReporter'])->name('all.reporter');

    // catogory subcategory
    Route::get('/admin/categories', [ReporterController::class, 'index'])->name('categories.index');
    Route::post('/admin/categories', [ReporterController::class, 'store'])->name('categories.store');
    Route::put('/admin/categories/{category}', [ReporterController::class, 'update'])->name('categories.update');
    Route::delete('/admin/categories/{category}', [ReporterController::class, 'destroy'])->name('categories.destroy');

    Route::post('/admin/subcategories', [ReporterController::class, 'substore'])->name('subcategories.store');
    Route::put('/admin/subcategories/{subcategory}', [ReporterController::class, 'subupdate'])->name('subcategories.update');
    Route::delete('/admin/subcategories/{subcategory}', [ReporterController::class, 'subdestroy'])->name('subcategories.destroy');

    Route::prefix('admin')->group(function () {
        Route::get('/news', [AdminNewsController::class, 'index'])->name('admin.news');
        Route::get('/news/create', [AdminNewsController::class, 'create'])->name('admin.news.create');
        Route::post('/news/store', [AdminNewsController::class, 'store'])->name('admin.news.store');

        Route::get('/news/edit/{id}', [AdminNewsController::class, 'edit'])->name('admin.news.edit');
        Route::post('/news/update/{id}', [AdminNewsController::class, 'update'])->name('admin.news.update');

        Route::delete('/news/delete/{id}', [AdminNewsController::class, 'delete'])->name('admin.news.delete');

        // pathock and reporetr
        Route::get('/pathock', [AdminController::class, 'pathock'])->name('admin.pathock');
        Route::get('/reporter', [AdminController::class, 'reporter'])->name('admin.reporter');
        Route::delete('/pathock/{id}', [AdminController::class, 'pathockDestroy'])->name('pathock.destroy');
        Route::put('/reporter/status/{id}', [ReporterController::class, 'updateStatus'])->name('reporters.updateStatus');

        // motamot routes
        Route::get('motamot/list', [MotamotController::class, 'motamotList'])->name('motamot.list');
        Route::get('motamot/user/{id}', [MotamotController::class, 'motamotUser'])->name('motamot.user');

        // Shop routes
        Route::resource('shops', ShopController::class);

        // products
        Route::resource('products', ProductController::class);

        // order routes
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');

        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

        // referral routes
        Route::get('/referral-tree', [ReferralController::class, 'index'])->name('referral.tree');

        // stock money routes
        Route::get('/stock-money', [StockMoneyController::class, 'stockMoney'])->name('admin.stock.money');
        Route::post('/stock-money/update-status/{id}', [StockMoneyController::class, 'updateStatus'])->name('stock-money.updateStatus');
    });
});
Route::post('/subscribe', [TermesController::class, 'subscribe'])->name('subscribe');

// store comment
Route::post('/news/{news}/comments', [UserController::class, 'storeComment']);

// product details
Route::resource('cart', CartController::class);
Route::get('/productdetails/{product}', [ProductDetailsController::class, 'show'])->name('product.show');

// order
Route::get('/order/create', [OrderController::class, 'create'])->name('order.create');
Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');

// extra link
Route::get('/saradesh/{division?}', [UserController::class, 'home'])->name('saradesh');

Route::get('/prayer-times', function () {
    $response = Http::get('https://api.aladhan.com/v1/timingsByCity', [
        'city' => 'Dhaka',
        'country' => 'Bangladesh',
        'method' => 3,
    ]);

    return response()->json($response->json());
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');

    return redirect()->back()->with('status', 'Storage link created successfully!');
});
