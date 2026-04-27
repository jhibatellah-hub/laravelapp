<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Mail\AppointmentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AppointmentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Appointment::with(['patient', 'doctor', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc');

        // Filtres rôle
        if ($user->isPatient()) {
            $query->forPatient($user->id);
        } elseif ($user->isDoctor()) {
            $query->forDoctor($user->id);
        }

        // Filtres requête
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        if ($request->filled('doctor_id') && ($user->isAdmin() || $user->isPatient())) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('doctor', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        $appointments = $query->paginate(15)->withQueryString();
        $doctors = User::doctors()->active()->get();
        $services = Service::active()->get();

        // ⬅️ ṢAḤḤAḤT: Stats selon rôle
        $stats = $this->getStatsForUser($user);

        return view('appointments.index', compact('appointments', 'doctors', 'services', 'stats'));
    }

    private function getStatsForUser($user): array
    {
        if ($user->isAdmin() || $user->isDoctor()) {
            return [
                'total' => Appointment::count(),
                'pending' => Appointment::pending()->count(),
                'confirmed' => Appointment::confirmed()->count(),
                'today' => Appointment::whereDate('appointment_date', today())->count(),
            ];
        }
        
        // Patient: ghir stats dyalou
        return [
            'my_total' => Appointment::forPatient($user->id)->count(),
            'my_pending' => Appointment::forPatient($user->id)->pending()->count(),
            'my_confirmed' => Appointment::forPatient($user->id)->confirmed()->count(),
            'my_today' => Appointment::forPatient($user->id)
                ->whereDate('appointment_date', today())->count(),
        ];
    }

    public function store(StoreAppointmentRequest $request)
    {
        $validated = $request->validated();
        $service = Service::find($validated['service_id']);

        if ($this->checkConflict(
            $validated['doctor_id'], 
            $validated['appointment_date'], 
            $validated['appointment_time'],
            $service->duration_minutes ?? 30
        )) {
            return back()
                ->withErrors(['appointment_time' => __('appointments.time_not_available')])
                ->withInput();
        }

        $appointment = Appointment::create($validated);

        // Envoyer email
        $this->sendConfirmationEmail($appointment);

        return redirect()->route('appointments.index')
            ->with('success', __('appointments.created_success'));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $validated = $request->validated();

        if (isset($validated['appointment_date']) || isset($validated['appointment_time']) || isset($validated['doctor_id'])) {
            $date = $validated['appointment_date'] ?? $appointment->appointment_date;
            $time = $validated['appointment_time'] ?? $appointment->appointment_time;
            $doctorId = $validated['doctor_id'] ?? $appointment->doctor_id;
            $service = Service::find($validated['service_id'] ?? $appointment->service_id);

            if ($this->checkConflict($doctorId, $date, $time, $service->duration_minutes ?? 30, $appointment->id)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false, 
                        'message' => __('appointments.time_not_available')
                    ], 422);
                }
                return back()
                    ->withErrors(['appointment_time' => __('appointments.time_not_available')])
                    ->withInput();
            }
        }

        $appointment->update($validated);

        if (isset($validated['status'])) {
            match($validated['status']) {
                'confirmed' => $appointment->confirm(),
                'cancelled' => $appointment->cancel('Annulé par l\'utilisateur'),
                default => null,
            };
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true, 
                'appointment' => $appointment->load('patient', 'doctor', 'service')
            ]);
        }

        return redirect()->route('appointments.index')
            ->with('success', __('appointments.updated_success'));
    }

    
    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);

        $appointment->cancel('Annulé par l\'utilisateur');

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('appointments.index')
            ->with('success', __('appointments.cancelled_success'));
    }

    public function search(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor', 'service']);

        if (Auth::user()->isPatient()) {
            $query->forPatient(Auth::id());
        } elseif (Auth::user()->isDoctor()) {
            $query->forDoctor(Auth::id());
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->whereHas('patient', fn($sq) => $sq->where('name', 'like', "%{$q}%"))
                      ->orWhereHas('doctor', fn($sq) => $sq->where('name', 'like', "%{$q}%"))
                      ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'patient_name' => $a->patient->name,
                'doctor_name' => $a->doctor->name,
                'service_name' => $a->service->name,
                'appointment_date' => $a->appointment_date->format('d/m/Y'),
                'appointment_time' => $a->appointment_time,
                'status' => $a->status,
                'status_label' => $a->status_label,
                'status_color' => $a->status_color,
            ]);

        return response()->json($appointments);
    }

    /**
     * ⬅️ ṢAḤḤAḤT: Kat7ess b duration dial service
     */
    private function checkConflict($doctorId, $date, $time, $durationMinutes = 30, $excludeAppointmentId = null)
    {
        return Appointment::conflicting($doctorId, $date, $time, $durationMinutes, $excludeAppointmentId)->exists();
    }

    private function sendConfirmationEmail(Appointment $appointment): void
    {
        try {
            Mail::to($appointment->patient->email)
                ->send(new AppointmentConfirmation($appointment));
            $appointment->update(['email_sent' => true]);
        } catch (\Exception $e) {
            logger()->error('Mail error: ' . $e->getMessage());
        }
    }
}