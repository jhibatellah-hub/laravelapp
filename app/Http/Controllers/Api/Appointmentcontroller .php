<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    /**
     * GET /api/appointments
     * Liste les rendez-vous avec filtres et pagination
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient:id,name,email,phone', 'doctor:id,name,specialty', 'service']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('patient_id')) {
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
     * Créer un nouveau rendez-vous
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id'       => 'required|exists:users,id',
            'doctor_id'        => 'required|exists:users,id',
            'service_id'       => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'notes'            => 'nullable|string|max:500',
        ]);

        // Vérifier conflit
        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Ce créneau n\'est pas disponible.',
                'errors'  => ['appointment_time' => ['Ce créneau est déjà réservé.']],
            ], 422);
        }

        $appointment = Appointment::create($validated);
        $appointment->load(['patient', 'doctor', 'service']);

        // Email
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
     * GET /api/appointments/{id}
     * Détail d'un rendez-vous
     */
    public function show(Appointment $appointment)
    {
        return response()->json([
            'data' => $appointment->load(['patient', 'doctor', 'service']),
        ]);
    }

    /**
     * PUT /api/appointments/{id}
     * Modifier un rendez-vous
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'doctor_id'           => 'sometimes|exists:users,id',
            'appointment_date'    => 'sometimes|date|after_or_equal:today',
            'appointment_time'    => 'sometimes|date_format:H:i',
            'status'              => 'sometimes|in:pending,confirmed,cancelled,completed',
            'notes'               => 'nullable|string|max:500',
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        // Modification zedtha hna: Vérifier les conflits si on change la date, l'heure ou le docteur
        if (isset($validated['appointment_date']) || isset($validated['appointment_time']) || isset($validated['doctor_id'])) {
            $date = $validated['appointment_date'] ?? $appointment->appointment_date;
            $time = $validated['appointment_time'] ?? $appointment->appointment_time;
            $doctorId = $validated['doctor_id'] ?? $appointment->doctor_id;

            $conflict = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $date)
                ->where('appointment_time', $time)
                ->where('id', '!=', $appointment->id) // N7iydo l'rendez-vous l7ali bach maydirch conflit m3a rasso
                ->whereNotIn('status', ['cancelled'])
                ->exists();

            if ($conflict) {
                return response()->json([
                    'message' => 'Ce créneau n\'est pas disponible.',
                    'errors'  => ['appointment_time' => ['Ce créneau est déjà réservé par un autre patient.']],
                ], 422);
            }
        }

        $appointment->update($validated);

        if (isset($validated['status'])) {
            if ($validated['status'] === 'confirmed') {
                $appointment->update(['confirmed_at' => now()]);
            } elseif ($validated['status'] === 'cancelled') {
                $appointment->update(['cancelled_at' => now()]);
            }
        }

        return response()->json([
            'message' => 'Rendez-vous mis à jour.',
            'data'    => $appointment->fresh(['patient', 'doctor', 'service']),
        ]);
    }

    /**
     * DELETE /api/appointments/{id}
     * Annuler un rendez-vous
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->cancel('Annulé via API');

        return response()->json([
            'message' => 'Rendez-vous annulé avec succès.',
        ]);
    }
}