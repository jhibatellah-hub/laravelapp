<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Créer les services ────────────────────────────────────────
        $services = [
            ['name' => 'Consultation générale', 'name_ar' => 'الاستشارة العامة',    'duration_minutes' => 30,  'price' => 150, 'color' => '#185FA5'],
            ['name' => 'Cardiologie',           'name_ar' => 'أمراض القلب',          'duration_minutes' => 45,  'price' => 350, 'color' => '#A32D2D'],
            ['name' => 'Pédiatrie',             'name_ar' => 'طب الأطفال',           'duration_minutes' => 30,  'price' => 200, 'color' => '#3B6D11'],
            ['name' => 'Radiologie',            'name_ar' => 'الأشعة',               'duration_minutes' => 60,  'price' => 400, 'color' => '#534AB7'],
            ['name' => 'Dermatologie',          'name_ar' => 'الجلدية',              'duration_minutes' => 30,  'price' => 250, 'color' => '#0F6E56'],
            ['name' => 'Ophtalmologie',         'name_ar' => 'طب العيون',            'duration_minutes' => 30,  'price' => 300, 'color' => '#854F0B'],
        ];

        foreach ($services as $s) {
            Service::create(array_merge($s, ['description' => null, 'is_active' => true]));
        }

        // ─── Admin ─────────────────────────────────────────────────────
        $admin = User::create([
            'name'     => 'Administrateur',
            'email'    => 'admin@medcabinet.ma',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'phone'    => '+212600000001',
            'is_active'=> true,
        ]);

        // ─── Médecins ──────────────────────────────────────────────────
        $doctors = [
            ['name' => 'Dr. Karim Rachidi',   'email' => 'medecin@medcabinet.ma',  'specialty' => 'Médecine générale', 'phone' => '+212600000002'],
            ['name' => 'Dr. Samira Lahlou',   'email' => 'lahlou@medcabinet.ma',   'specialty' => 'Cardiologie',       'phone' => '+212600000003'],
            ['name' => 'Dr. Youssef Bennani', 'email' => 'bennani@medcabinet.ma',  'specialty' => 'Pédiatrie',         'phone' => '+212600000004'],
        ];

        $doctorUsers = [];
        foreach ($doctors as $d) {
            $doctorUsers[] = User::create(array_merge($d, [
                'password'  => Hash::make('password'),
                'role'      => 'doctor',
                'is_active' => true,
            ]));
        }

        // ─── Patients ──────────────────────────────────────────────────
        $patients = [
            ['name' => 'Ahmed Benali',       'email' => 'patient@medcabinet.ma',  'phone' => '+212661000001', 'birth_date' => '1985-03-15'],
            ['name' => 'Fatima Zohra',       'email' => 'fatima@example.com',     'phone' => '+212661000002', 'birth_date' => '1990-07-22'],
            ['name' => 'Mohammed Alami',     'email' => 'alami@example.com',      'phone' => '+212661000003', 'birth_date' => '1978-11-30'],
            ['name' => 'Sara Tazi',          'email' => 'sara@example.com',       'phone' => '+212661000004', 'birth_date' => '1995-01-08'],
            ['name' => 'Karim Mansouri',     'email' => 'karim@example.com',      'phone' => '+212661000005', 'birth_date' => '1988-06-14'],
            ['name' => 'Nadia El Fassi',     'email' => 'nadia@example.com',      'phone' => '+212661000006', 'birth_date' => '1992-09-03'],
            ['name' => 'Omar Senhaji',       'email' => 'omar@example.com',       'phone' => '+212661000007', 'birth_date' => '1975-12-20'],
            ['name' => 'Houda Berrada',      'email' => 'houda@example.com',      'phone' => '+212661000008', 'birth_date' => '2001-04-17'],
            ['name' => 'Yassine Chaoui',     'email' => 'yassine@example.com',    'phone' => '+212661000009', 'birth_date' => '1983-08-25'],
            ['name' => 'Zineb Arabi',        'email' => 'zineb@example.com',      'phone' => '+212661000010', 'birth_date' => '1997-02-11'],
        ];

        $patientUsers = [];
        foreach ($patients as $p) {
            $patientUsers[] = User::create(array_merge($p, [
                'password'  => Hash::make('password'),
                'role'      => 'patient',
                'is_active' => true,
            ]));
        }

        // ─── Rendez-vous (20 minimum) ──────────────────────────────────
        $appointmentsData = [
            // Passés confirmés
            ['patient' => 0, 'doctor' => 0, 'service' => 0, 'date' => '-10 days', 'time' => '09:00', 'status' => 'completed'],
            ['patient' => 1, 'doctor' => 1, 'service' => 1, 'date' => '-8 days',  'time' => '10:30', 'status' => 'completed'],
            ['patient' => 2, 'doctor' => 2, 'service' => 2, 'date' => '-7 days',  'time' => '14:00', 'status' => 'completed'],
            ['patient' => 3, 'doctor' => 0, 'service' => 3, 'date' => '-5 days',  'time' => '11:00', 'status' => 'completed'],
            ['patient' => 4, 'doctor' => 1, 'service' => 4, 'date' => '-4 days',  'time' => '09:30', 'status' => 'cancelled'],
            ['patient' => 5, 'doctor' => 2, 'service' => 5, 'date' => '-3 days',  'time' => '15:00', 'status' => 'completed'],
            ['patient' => 6, 'doctor' => 0, 'service' => 0, 'date' => '-2 days',  'time' => '08:30', 'status' => 'cancelled'],
            ['patient' => 7, 'doctor' => 1, 'service' => 1, 'date' => '-1 day',   'time' => '10:00', 'status' => 'completed'],
            // Aujourd'hui
            ['patient' => 0, 'doctor' => 0, 'service' => 0, 'date' => 'today',    'time' => '09:00', 'status' => 'confirmed'],
            ['patient' => 1, 'doctor' => 1, 'service' => 1, 'date' => 'today',    'time' => '10:30', 'status' => 'confirmed'],
            ['patient' => 2, 'doctor' => 2, 'service' => 2, 'date' => 'today',    'time' => '11:30', 'status' => 'pending'],
            ['patient' => 8, 'doctor' => 0, 'service' => 3, 'date' => 'today',    'time' => '14:00', 'status' => 'confirmed'],
            // Futurs
            ['patient' => 3, 'doctor' => 1, 'service' => 4, 'date' => '+1 day',   'time' => '09:30', 'status' => 'confirmed'],
            ['patient' => 4, 'doctor' => 2, 'service' => 5, 'date' => '+1 day',   'time' => '11:00', 'status' => 'pending'],
            ['patient' => 5, 'doctor' => 0, 'service' => 0, 'date' => '+2 days',  'time' => '08:30', 'status' => 'confirmed'],
            ['patient' => 6, 'doctor' => 1, 'service' => 1, 'date' => '+2 days',  'time' => '14:30', 'status' => 'pending'],
            ['patient' => 7, 'doctor' => 2, 'service' => 2, 'date' => '+3 days',  'time' => '10:00', 'status' => 'confirmed'],
            ['patient' => 9, 'doctor' => 0, 'service' => 3, 'date' => '+4 days',  'time' => '09:00', 'status' => 'pending'],
            ['patient' => 0, 'doctor' => 1, 'service' => 4, 'date' => '+5 days',  'time' => '15:30', 'status' => 'confirmed'],
            ['patient' => 1, 'doctor' => 2, 'service' => 5, 'date' => '+7 days',  'time' => '11:00', 'status' => 'pending'],
        ];

        $serviceModels = Service::all();

        foreach ($appointmentsData as $a) {
            $date = match(true) {
                str_contains($a['date'], 'today')    => now()->toDateString(),
                str_contains($a['date'], '+')        => now()->modify($a['date'])->toDateString(),
                str_contains($a['date'], '-')        => now()->modify($a['date'])->toDateString(),
                default                              => $a['date'],
            };

            Appointment::create([
                'patient_id'       => $patientUsers[$a['patient']]->id,
                'doctor_id'        => $doctorUsers[$a['doctor']]->id,
                'service_id'       => $serviceModels[$a['service']]->id,
                'appointment_date' => $date,
                'appointment_time' => $a['time'],
                'status'           => $a['status'],
                'email_sent'       => in_array($a['status'], ['confirmed', 'completed']),
                'confirmed_at'     => in_array($a['status'], ['confirmed', 'completed']) ? now() : null,
                'cancelled_at'     => $a['status'] === 'cancelled' ? now() : null,
            ]);
        }

        $this->command->info(' Base de données initialisée avec succès !');
        $this->command->info('   Admin    : admin@medcabinet.ma / password');
        $this->command->info('   Médecin  : medecin@medcabinet.ma / password');
        $this->command->info('   Patient  : patient@medcabinet.ma / password');
    }
}