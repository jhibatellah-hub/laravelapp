<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentConfirmation;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor', 'service'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc');

        // Filtres rôle
        if (Auth::user()->isPatient()) {
            $query->forPatient(Auth::id());
        } elseif (Auth::user()->isDoctor()) {
            $query->forDoctor(Auth::id());
        }

        // Filtres requête
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('doctor', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('service', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $appointments = $query->paginate(15)->withQueryString();
        $doctors      = User::doctors()->active()->get();
        $services     = Service::active()->get();

        // Stats pour la vue
        $stats = [
            'total'     => Appointment::count(),
            'pending'   => Appointment::pending()->count(),
            'confirmed' => Appointment::confirmed()->count(),
            'today'     => Appointment::whereDate('appointment_date', today())->count(),
        ];

        return view('appointments.index', compact('appointments', 'doctors', 'services', 'stats'));
    }

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

        // Vérifier disponibilité
        $conflict = Appointment::where('doctor_id', $validated['doctor_id'])
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereNotIn('status', ['cancelled'])
            ->exists();

        if ($conflict) {
            return back()->withErrors(['appointment_time' => __('appointments.time_not_available')])->withInput();
        }

        $appointment = Appointment::create($validated);

        // Envoyer email de confirmation
        try {
            Mail::to($appointment->patient->email)
                ->send(new AppointmentConfirmation($appointment));
            $appointment->update(['email_sent' => true]);
        } catch (\Exception $e) {
            logger()->error('Mail error: ' . $e->getMessage());
        }

        return redirect()->route('appointments.index')
            ->with('success', __('appointments.created_success'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'patient_id'       => 'sometimes|exists:users,id',
            'doctor_id'        => 'sometimes|exists:users,id',
            'service_id'       => 'sometimes|exists:services,id',
            'appointment_date' => 'sometimes|date',
            'appointment_time' => 'sometimes|date_format:H:i',
            'status'           => 'sometimes|in:pending,confirmed,cancelled,completed',
            'notes'            => 'nullable|string|max:500',
        ]);

        $appointment->update($validated);

        if (isset($validated['status'])) {
            if ($validated['status'] === 'confirmed') {
                $appointment->update(['confirmed_at' => now()]);
                // Renvoyer email si confirmation
                try {
                    Mail::to($appointment->patient->email)
                        ->send(new AppointmentConfirmation($appointment));
                } catch (\Exception $e) {
                    logger()->error('Mail error: ' . $e->getMessage());
                }
            } elseif ($validated['status'] === 'cancelled') {
                $appointment->update(['cancelled_at' => now()]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'appointment' => $appointment->load('patient', 'doctor', 'service')]);
        }

        return redirect()->route('appointments.index')
            ->with('success', __('appointments.updated_success'));
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->cancel('Annulé par l\'utilisateur');

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('appointments.index')
            ->with('success', __('appointments.cancelled_success'));
    }

    /**
     * Recherche AJAX (Axios)
     */
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
                      ->orWhereHas('doctor',  fn($sq) => $sq->where('name', 'like', "%{$q}%"))
                      ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$q}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->limit(20)->get()
            ->map(fn($a) => [
                'id'               => $a->id,
                'patient_name'     => $a->patient->name,
                'doctor_name'      => $a->doctor->name,
                'service_name'     => $a->service->name,
                'appointment_date' => $a->appointment_date->format('d/m/Y'),
                'appointment_time' => $a->appointment_time,
                'status'           => $a->status,
                'status_label'     => $a->status_label,
            ]);

        return response()->json($appointments);
    }
}