<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'user' => auth()->user(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin/dashboard', function () {
    return Inertia::render('Admin/Dashboard', [
        'user' => auth()->user(),
    ]);
})->middleware(['auth', 'verified', 'role:admin'])->name('admin.dashboard');

Route::get('/staff/dashboard', function () {
    return Inertia::render('Staff/Dashboard', [
        'user' => auth()->user(),
    ]);
})->middleware(['auth', 'verified', 'role:staff'])->name('staff.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
