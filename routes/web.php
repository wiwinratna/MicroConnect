<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// GUEST (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    // REGISTER / SIGN UP
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.process');
});

// LOGOUT (butuh login)
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ADMIN AREA (cukup auth, role dicek lewat redirect waktu login)
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

// UMKM / PELAKU USAHA AREA (auth + pelakuusaha)
Route::middleware(['auth', 'pelakuusaha'])->group(function () {
    Route::get('/umkm/dashboard', function () {
        return view('umkm.dashboard');
    })->name('umkm.dashboard');
});
