<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin() || 
               $user->id === $appointment->doctor_id || 
               $user->id === $appointment->patient_id;
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin() || 
               $user->id === $appointment->doctor_id || 
               ($user->id === $appointment->patient_id && $appointment->status === 'pending');
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->isAdmin() || 
               $user->id === $appointment->doctor_id || 
               $user->id === $appointment->patient_id;
    }
}