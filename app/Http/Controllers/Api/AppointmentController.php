<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    /**
     * GET /api/appointments
     * Liste les rendez-vous avec filtres et sécurité des rôles
     */
    public function index(Request $request)
    {
        $user = $request->user(); 
        
        $query = Appointment::with(['patient:id,name,email,phone', 'doctor:id,name,specialty', 'service']);

        
        if ($user->isPatient()) {
            $query->forPatient($user->id);
        } elseif ($user->isDoctor()) {
            $query->forDoctor($user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        // L'Admin w tbib homa li yqadrou yfiltriw b patient_id
        if ($request->filled('patient_id') && !$user->isPatient()) {
            $query->where('patient_id', $request->patient_id);
        }

        $perPage = min($request->input('per_page', 15), 100);
        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $appointments->items(),
            'meta' => [
                'current_page' => $appointments->currentPage(),
                'last_page'    => $appointments->lastPage(),
                'per_page'     => $appointments->perPage(),
                'total'        => $appointments->total(),
            ],
        ]);
    }

    /**
     * POST /api/appointments
     * Creer un nouveau rendez-vous
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'patient_id'       => 'required|exists:users,id',
            'doctor_id'        => 'required|exists:users,id',
            'service_id'       => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'notes'            => 'nullable|string|max:500',
        ]);

        if ($user->isPatient()) {
            $validated['patient_id'] = $user->id;
        }

        $service = Service::find($validated['service_id']);
        
        $conflict = Appointment::conflicting(
            $validated['doctor_id'], 
            $validated['appointment_date'], 
            $validated['appointment_time'],
            $service->duration_minutes ?? 30
        )->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Ce créneau n\'est pas disponible.',
                'errors'  => ['appointment_time' => ['Ce créneau est déjà réservé.']],
            ], 422);
        }

        $appointment = Appointment::create($validated);
        $appointment->load(['patient', 'doctor', 'service']);

        try {
            Mail::to($appointment->patient->email)
                ->send(new AppointmentConfirmation($appointment));
            $appointment->update(['email_sent' => true]);
        } catch (\Exception $e) {
            logger()->error('API Mail error: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Rendez-vous créé avec succès.',
            'data'    => $appointment,
        ], 201);
    }

    /**
     * Detail d'un rendez-vous
     */
    public function show(Request $request, Appointment $appointment)
    {
        $user = $request->user();

        if ($user->isPatient() && $appointment->patient_id !== $user->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        return response()->json([
            'data' => $appointment->load(['patient', 'doctor', 'service']),
        ]);
    }

    /**
     * Modifier un rendez-vous
     */
    public function update(Request $request, Appointment $appointment)
    {
        $user = $request->user();

        // SÉCURITÉ
        if ($user->isPatient() && $appointment->patient_id !== $user->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $validated = $request->validate([
            'doctor_id'           => 'sometimes|exists:users,id',
            'service_id'          => 'sometimes|exists:services,id',
            'appointment_date'    => 'sometimes|date|after_or_equal:today',
            'appointment_time'    => 'sometimes|date_format:H:i',
            'status'              => 'sometimes|in:pending,confirmed,cancelled,completed',
            'notes'               => 'nullable|string|max:500',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        if (isset($validated['appointment_date']) || isset($validated['appointment_time']) || isset($validated['doctor_id'])) {
            $date = $validated['appointment_date'] ?? $appointment->appointment_date;
            $time = $validated['appointment_time'] ?? $appointment->appointment_time;
            $doctorId = $validated['doctor_id'] ?? $appointment->doctor_id;
            $service = Service::find($validated['service_id'] ?? $appointment->service_id);

            // Kheddemna scope `conflicting`
            $conflict = Appointment::conflicting($doctorId, $date, $time, $service->duration_minutes ?? 30, $appointment->id)->exists();

            if ($conflict) {
                return response()->json([
                    'message' => 'Ce créneau n\'est pas disponible.',
                    'errors'  => ['appointment_time' => ['Ce créneau est déjà réservé par un autre patient.']],
                ], 422);
            }
        }

        $appointment->update($validated);

        if (isset($validated['status'])) {
            match($validated['status']) {
                'confirmed' => $appointment->confirm(),
                'cancelled' => $appointment->cancel($validated['cancellation_reason'] ?? 'Annulé via API'),
                default => null,
            };
        }

        return response()->json([
            'message' => 'Rendez-vous mis à jour.',
            'data'    => $appointment->fresh(['patient', 'doctor', 'service']),
        ]);
    }

    /**
     * Annuler un rendez-vous
     */
    public function destroy(Request $request, Appointment $appointment)
    {
        $user = $request->user();

        // SÉCURITÉ
        if (!$user->isAdmin() && $appointment->patient_id !== $user->id) {
            return response()->json(['message' => 'Accès non autorisé.'], 403);
        }

        $appointment->cancel('Annulé via API');
        $appointment->delete();

        return response()->json([
            'message' => 'Rendez-vous annulé et supprimé avec succès.',
        ]);
    }
}