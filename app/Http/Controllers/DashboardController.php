<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        $stats = [
            'total_appointments'    => Appointment::count(),
            'pending_appointments'  => Appointment::pending()->count(),
            'confirmed_appointments'=> Appointment::confirmed()->count(),
            'today_appointments'    => Appointment::whereDate('appointment_date', today())->count(),
            'total_patients'        => User::patients()->count(),
            'total_doctors'         => User::doctors()->count(),
            'cancelled_this_week'   => Appointment::where('status', 'cancelled')
                                        ->whereBetween('cancelled_at', [now()->startOfWeek(), now()->endOfWeek()])
                                        ->count(),
        ];

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

        // Graphique: RDV des 7 derniers jours
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartData[] = [
                'date'  => $date->format('d/m'),
                'count' => Appointment::whereDate('appointment_date', $date->toDateString())->count(),
            ];
        }

        return view('dashboard.index', compact('stats', 'upcomingAppointments', 'chartData'));
    }

    public function setLocale(string $locale)
    {
        if (in_array($locale, ['fr', 'ar'])) {
            session(['locale' => $locale]);
            Auth::user()?->update(['locale' => $locale]);
        }
        return redirect()->back();
    }
}