<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Les 5 prochains rendez-vous
        $upcomingQuery = Appointment::with(['patient', 'doctor', 'service'])
            ->upcoming()
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');

        if ($user->isDoctor()) {
            $upcomingQuery->forDoctor($user->id);
        } elseif ($user->isPatient()) {
            $upcomingQuery->forPatient($user->id);
        }
        
        $upcomingAppointments = $upcomingQuery->limit(5)->get();

        $stats = [];
        $chartData = [];

        // 2. Séparation dyal l'Affichage (Admin/Tbib vs Patient)
        if ($user->isAdmin() || $user->isDoctor()) {
            
            // Stats globales
            $stats = [
                'total_appointments'     => Appointment::count(),
                'pending_appointments'   => Appointment::pending()->count(),
                'confirmed_appointments' => Appointment::confirmed()->count(),
                'total_patients'         => User::patients()->count(),
            ];

            // Graphique optimisé
            $last7Days = now()->subDays(6)->startOfDay();
            $appointmentsLast7Days = Appointment::where('appointment_date', '>=', $last7Days)
                ->get()
                ->groupBy(fn($app) => $app->appointment_date->format('d/m'))
                ->map(fn($group) => $group->count());

            for ($i = 6; $i >= 0; $i--) {
                $dateString = now()->subDays($i)->format('d/m');
                $chartData[] = [
                    'date'  => $dateString,
                    'count' => $appointmentsLast7Days->get($dateString, 0), 
                ];
            }

        } else {
            // Stats dyal l'Patient
            $stats = [
                'my_total_appointments' => Appointment::forPatient($user->id)->count(),
                'my_pending'            => Appointment::forPatient($user->id)->pending()->count(),
                'my_confirmed'          => Appointment::forPatient($user->id)->confirmed()->count(),
            ];
        }

        return view('dashboard.index', compact('stats', 'upcomingAppointments', 'chartData'));
    }

    public function setLocale(string $locale)
    {
        // Khllina ghir l'Français w l'Anglais
        if (in_array($locale, ['fr', 'en'])) {
            session(['locale' => $locale]);
            
            if (Auth::check()) {
                Auth::user()->update(['locale' => $locale]);
            }
        }
        return redirect()->back();
    }
}