<?php

use App\Http\Controllers\FrontEnd\FooterController;
use App\Http\Controllers\FrontEnd\HeaderController;
use App\Http\Controllers\FrontEnd\TermesController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
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
});
