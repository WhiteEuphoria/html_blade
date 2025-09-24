<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ClientLoginController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\DocumentUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/dashboard', 'dashboard')
    ->middleware('auth')
    ->name('dashboard');


// === Theme/Static integration routes (disabled by default) ===
if (config('integration.theme_routes')) {
    Route::view('/', 'main.index')->name('home');
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [ClientLoginController::class, 'store'])->name('login.attempt');
    Route::view('/register', 'auth.register')->name('register');
    Route::view('/verify', 'auth.verify')->name('verification.notice');
    Route::get('/user', ClientDashboardController::class)
        ->middleware('auth')
        ->name('user.dashboard');
    Route::view('/user/withdraw', 'user.withdraw')->name('user.withdraw');
    Route::view('/user/violation', 'user.violation')->name('user.violation');
    Route::view('/enter', 'enter')->name('enter');
    Route::post('/enter', DocumentUploadController::class)
        ->name('documents.store');
    Route::prefix('admin')->group(function () {
        Route::view('/login', 'admin.login')->name('filament.admin.auth.login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('admin.login.attempt');

        Route::middleware(['auth', 'admin'])->group(function () {
            Route::redirect('/', '/admin/dashboard')->name('admin.home');
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
            Route::post('/dashboard/support', [AdminDashboardController::class, 'storeSupport'])->name('admin.dashboard.support');
            Route::post('/dashboard/withdrawals', [AdminDashboardController::class, 'storeWithdrawal'])->name('admin.dashboard.withdrawals.store');
            Route::post('/dashboard/fraud-claims', [AdminDashboardController::class, 'storeFraudClaim'])->name('admin.dashboard.fraud-claims.store');
            Route::post('/dashboard/accounts', [AdminDashboardController::class, 'storeAccount'])->name('admin.dashboard.accounts.store');
        });
    });
}
// When disabled, Filament panels handle /admin and /client entirely.
