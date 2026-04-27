<?php

use App\Http\Controllers\Api\AppointmentController;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    // Rendez-vous CRUD
    Route::apiResource('appointments', AppointmentController::class)->names([
        'index' => 'api.appointments.index',
        'store' => 'api.appointments.store',
        'show' => 'api.appointments.show',
        'update' => 'api.appointments.update',
        'destroy' => 'api.appointments.destroy',
    ]);
    
    // Patients
    Route::get('/patients', function () {
        return response()->json(
            User::patients()->active()->get(['id', 'name', 'email', 'phone'])
        );
    });

    Route::get('/patients/{user}', function (User $user) {
        return response()->json(
            $user->load('appointmentsAsPatient.doctor', 'appointmentsAsPatient.service')
        );
    });

    Route::get('/patients/{user}/appointments', function (User $user) {
        return response()->json(
            $user->appointmentsAsPatient()->with(['doctor', 'service'])->paginate(15)
        );
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
