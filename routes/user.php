<?php

use App\Http\Controllers\Admin\FamilyController;
use App\Http\Controllers\Admin\LookupController;
use App\Http\Controllers\Admin\ReporterActivityController;
use App\Http\Controllers\Admin\UserChangePassword;
use App\Http\Controllers\FrontEnd\FooterController;
use App\Http\Controllers\FrontEnd\HeaderController;
use App\Http\Controllers\FrontEnd\TermesController;
use App\Http\Controllers\FrontEnd\VideoController;
use Illuminate\Support\Facades\Route;

// frontend route 
Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');

// backend admin route 
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/headers', [HeaderController::class, 'index'])->name('headers.index');
    Route::post('/headers', [HeaderController::class, 'store'])->name('headers.store');
    Route::put('/headers/{header}', [HeaderController::class, 'update'])->name('headers.update');
    Route::delete('/headers/{header}', [HeaderController::class, 'destroy'])->name('headers.destroy');

    Route::get('/footers', [FooterController::class, 'index'])->name('footers.index');
    Route::post('/footers', [FooterController::class, 'store'])->name('footers.store');
    Route::put('/footers/{footer}', [FooterController::class, 'update'])->name('footers.update');
    Route::delete('/footers/{footer}', [FooterController::class, 'destroy'])->name('footers.destroy');

    Route::get('/termes&condition', [TermesController::class, 'termesCondition'])->name('termes.condition');
    Route::post('/termes&conditions', [TermesController::class, 'storeOrUpdate'])->name('terms.storeOrUpdate');

    Route::resource('families', FamilyController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('/users/changepassword', [UserChangePassword::class, 'index'])->name('users.changePassword.index');
    Route::get('/users/list/changepassword', [UserChangePassword::class, 'list'])->name('users.changePassword.list');
    Route::post('/{user}/change-password', [UserChangePassword::class, 'changePassword'])->name('users.changePassword');

    // looksup routes 
    Route::get('/lookups', [LookupController::class, 'index'])->name('lookups.index');
    Route::get('/lookups/{table}/list', [LookupController::class, 'list'])->name('lookups.list');
    Route::get('/lookups/{table}/options', [LookupController::class, 'options'])->name('lookups.options');
    Route::post('/lookups/{table}', [LookupController::class, 'store'])->name('lookups.store');
    Route::put('/lookups/{table}/{id}', [LookupController::class, 'update'])->name('lookups.update');
    Route::delete('/lookups/{table}/{id}', [LookupController::class, 'destroy'])->name('lookups.destroy');

    // inactive route 
    Route::get('/reporters/inactive', [ReporterActivityController::class, 'index'])->name('reporters.inactive.index');
    Route::get('/reporters/inactive/list', [ReporterActivityController::class, 'list'])->name('reporters.inactive.list');
    Route::get('/reporters/inactive/{user}', [ReporterActivityController::class, 'details'])->name('reporters.inactive.details');
});
