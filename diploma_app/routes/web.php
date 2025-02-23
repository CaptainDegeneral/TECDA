<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    /**
     * Эндпоинты для CRUD-операций с дисциплинами
     */
    Route::controller(SubjectController::class)->name('subject.')->prefix('subjects')->group(function () {
       Route::get('/', 'index')->name('index');
       Route::get('{subject}', 'show')->name('show');
       Route::post('/', 'store')->name('store');
       Route::put('{subject}', 'edit')->name('edit');
       Route::delete('{subject}', 'destroy')->name('destroy');
    });
});

require __DIR__.'/auth.php';
