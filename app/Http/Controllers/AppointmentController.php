<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
// Ila mazal masawbtich l'mail, ghanhbtouha f try catch bla matdir l'erreur

class AppointmentController extends Controller
{
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

        // Filtres requête (Recherche normale)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        
        $appointments = $query->paginate(15)->withQueryString();
        $doctors = User::doctors()->active()->get();
        $services = Service::active()->get();
        $patients = User::patients()->active()->get(); // Zdnaha bach n3mro biha select dyal l'admin

        $stats = $this->getStatsForUser($user);

        return view('Appointments.Index', compact('appointments', 'doctors', 'patients', 'services', 'stats'));
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
        
        return [
            'total' => Appointment::forPatient($user->id)->count(),
            'pending' => Appointment::forPatient($user->id)->pending()->count(),
            'confirmed' => Appointment::forPatient($user->id)->confirmed()->count(),
            'today' => Appointment::forPatient($user->id)->whereDate('appointment_date', today())->count(),
        ];
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'notes' => 'nullable|string',
        ];

        if ($user->isPatient()) {
            $rules['doctor_id'] = ['required', Rule::exists('users', 'id')->where(fn ($query) => $query
                ->where('role', 'doctor')
                ->where('is_active', true))];
        } elseif ($user->isDoctor()) {
            $rules['patient_id'] = ['required', Rule::exists('users', 'id')->where(fn ($query) => $query
                ->where('role', 'patient')
                ->where('is_active', true))];
        } else {
            $rules['patient_id'] = ['required', Rule::exists('users', 'id')->where(fn ($query) => $query
                ->where('role', 'patient')
                ->where('is_active', true))];
            $rules['doctor_id'] = ['required', Rule::exists('users', 'id')->where(fn ($query) => $query
                ->where('role', 'doctor')
                ->where('is_active', true))];
        }

        $validated = $request->validate($rules);

        if ($user->isPatient()) {
            $validated['patient_id'] = $user->id;
        }

        if ($user->isDoctor()) {
            $validated['doctor_id'] = $user->id;
        }

        $service = Service::findOrFail($validated['service_id']);

        if ($this->checkConflict(
            $validated['doctor_id'], 
            $validated['appointment_date'], 
            $validated['appointment_time'],
            $service->duration_minutes ?? 30
        )) {
            return back()
                ->withErrors(['appointment_time' => 'Ce créneau n\'est pas disponible.'])
                ->withInput();
        }

        $appointment = Appointment::create($validated);

        // Envoyer email (Mkhbya f try/catch bach ila makanch l'email msawb matehbesh l'app)
        $this->sendConfirmationEmail($appointment);

        return redirect()->route('appointments.index')
            ->with('success', 'Rendez-vous créé avec succès.');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date' => 'sometimes|date|after_or_equal:today',
            'appointment_time' => 'sometimes',
            'doctor_id' => 'sometimes|exists:users,id',
            'service_id' => 'sometimes|exists:services,id',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'notes' => 'nullable|string',
        ]);

        if ($request->has('appointment_date') || $request->has('appointment_time') || $request->has('doctor_id')) {
            $date = $validated['appointment_date'] ?? $appointment->appointment_date;
            $time = $validated['appointment_time'] ?? $appointment->appointment_time;
            $doctorId = $validated['doctor_id'] ?? $appointment->doctor_id;
            $service = Service::find($validated['service_id'] ?? $appointment->service_id);

            if ($this->checkConflict($doctorId, $date, $time, $service->duration_minutes ?? 30, $appointment->id)) {
                return back()->withErrors(['appointment_time' => 'Ce créneau n\'est pas disponible.'])->withInput();
            }
        }

        $appointment->update($validated);

        if ($request->has('status')) {
            match($validated['status']) {
                'confirmed' => $appointment->confirm(),
                'cancelled' => $appointment->cancel('Annulé par l\'utilisateur'),
                default => null,
            };
        }

        return redirect()->route('appointments.index')
            ->with('success', 'Rendez-vous mis à jour avec succès.');
    }

    public function destroy(Appointment $appointment)
    {
        // 7iydna l'policy lmo3aqda w darna check normal
        if (!Auth::user()->isAdmin() && Auth::id() !== $appointment->patient_id) {
            abort(403, 'Accès non autorisé');
        }

        $appointment->cancel('Annulé par l\'utilisateur');
        $appointment->delete(); // Supprimer de la base de données

        return redirect()->route('appointments.index')
            ->with('success', 'Rendez-vous supprimé avec succès.');
    }

    // Fonction khassa b Axios (Recherche asynchrone)
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
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->whereHas('patient', fn($sq) => $sq->where('name', 'like', "%{$q}%"))
                         ->orWhereHas('doctor', fn($sq) => $sq->where('name', 'like', "%{$q}%"))
                         ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$q}%"));
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->limit(20)
            ->get()
            ->map(function($a) {
                return [
                    'id' => $a->id,
                    'patient_id' => $a->patient_id,
                    'doctor_id' => $a->doctor_id,
                    'service_id' => $a->service_id,
                    'patient_name' => $a->patient->name,
                    'doctor_name' => $a->doctor->name,
                    'doctor_specialty' => $a->doctor->specialty,
                    'service_name' => $a->service->name,
                    'duration_minutes' => $a->service->duration_minutes ?? 30,
                    'appointment_date' => $a->appointment_date->format('Y-m-d'),
                    'appointment_time' => $a->appointment_time,
                    'status' => $a->status,
                    'notes' => $a->notes,
                    'status_label' => $a->status_label ?? ucfirst($a->status),
                ];
            });

        return response()->json($appointments);
    }

    public function storePatient(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acces non autorise');
        }

        $validated = $request->validateWithBag('addPatient', [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'birth_date' => $validated['birth_date'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'patient',
            'locale' => app()->getLocale(),
            'is_active' => true,
        ]);

        return redirect()->route('appointments.index')->with('success', 'Patient ajoute avec succes.');
    }

    public function storeDoctor(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acces non autorise');
        }

        $validated = $request->validateWithBag('addDoctor', [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'specialty' => 'nullable|string|max:255',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'specialty' => $validated['specialty'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'doctor',
            'locale' => app()->getLocale(),
            'is_active' => true,
        ]);

        return redirect()->route('appointments.index')->with('success', 'Medecin ajoute avec succes.');
    }

    public function destroyManagedUser(User $user)
    {
        $authUser = Auth::user();

        if (!$authUser->isAdmin()) {
            abort(403, 'Acces non autorise');
        }

        if (!in_array($user->role, ['patient', 'doctor'], true)) {
            return redirect()->route('appointments.index')->with('error', 'Impossible de supprimer cet utilisateur.');
        }

        if ($authUser->id === $user->id) {
            return redirect()->route('appointments.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('appointments.index')->with('success', 'Utilisateur supprime avec succes.');
    }

    public function updateManagedUser(Request $request)
    {
        $authUser = Auth::user();

        if (!$authUser->isAdmin()) {
            abort(403, 'Acces non autorise');
        }

        $validated = $request->validateWithBag('editManagedUser', [
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'required|in:patient,doctor',
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->input('user_id')),
            ],
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'specialty' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if (!in_array($user->role, ['patient', 'doctor'], true)) {
            return redirect()->route('appointments.index')->with('error', 'Impossible de modifier cet utilisateur.');
        }

        if ($user->role !== $validated['role']) {
            return redirect()->route('appointments.index')->with('error', 'Le role ne correspond pas a l utilisateur.');
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ];

        if ($validated['role'] === 'patient') {
            $payload['birth_date'] = $validated['birth_date'] ?? null;
        } else {
            $payload['specialty'] = $validated['specialty'] ?? null;
        }

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $user->update($payload);

        return redirect()->route('appointments.index')->with('success', 'Utilisateur mis a jour avec succes.');
    }

    private function checkConflict($doctorId, $date, $time, $durationMinutes = 30, $excludeAppointmentId = null)
    {
        return Appointment::conflicting($doctorId, $date, $time, $durationMinutes, $excludeAppointmentId)->exists();
    }

    private function sendConfirmationEmail(Appointment $appointment): void
    {
        // Kan checkiw wesh l'email khddam bla matcrash l'app
        try {
            if (class_exists(\App\Mail\AppointmentConfirmation::class)) {
                Mail::to($appointment->patient->email)->send(new \App\Mail\AppointmentConfirmation($appointment));
                $appointment->update(['email_sent' => true]);
            }
        } catch (\Exception $e) {
            \Log::error('Mail error: ' . $e->getMessage());
        }
    }
}
