<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Authentification
Route::middleware('guest')->group(function () {
    Route::get('/login',[AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',[AuthController::class, 'login']);
    Route::get('/register',[AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Application
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/locale/{locale}', [DashboardController::class, 'setLocale'])->name('locale');

    // Rendez-vous
    Route::get('/appointments',[AppointmentController::class, 'index'])->name('appointments.index');
    Route::post('/appointments',[AppointmentController::class, 'store'])->name('appointments.store');
    Route::put('/appointments/{appointment}',[AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    // Recherche AJAX
    Route::get('/appointments/search', [AppointmentController::class, 'search'])->name('appointments.search');
});