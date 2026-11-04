<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->withoutMiddleware([
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
])->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/register', [SetupController::class, 'show'])->name('register');
    Route::post('/register', [SetupController::class, 'store'])->middleware('throttle:setup');
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
});
Route::get('/admin', AdminController::class)->middleware(['auth', 'admin'])->name('admin');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
