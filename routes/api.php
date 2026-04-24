<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentification API
Route::post('/auth/login', [AuthController::class, 'apiLogin']);

// Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Rendez-vous CRUD
    Route::apiResource('appointments', AppointmentController::class);

    // Patients
    Route::get('/patients', function () {
        return response()->json(User::patients()->active()->get(['id', 'name', 'email', 'phone']));
    });

    Route::get('/patients/{user}', function (User $user) {
        return response()->json($user->load('appointmentsAsPatient.doctor', 'appointmentsAsPatient.service'));
    });

    Route::get('/patients/{user}/appointments', function (User $user) {
        return response()->json($user->appointmentsAsPatient()->with(['doctor', 'service'])->paginate(15));
    });

    // Services
    Route::get('/services', function () {
        return response()->json(Service::active()->get());
    });

    // Utilisateur connecté
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });
});