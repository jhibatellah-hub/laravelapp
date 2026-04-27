<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $appointment = $this->route('appointment');
        $user = auth()->user();
        
        // Admin w Doctor y9derou ybeddelou koulchi
        // Patient ghir RDV dyalou
        return $user->isAdmin() || $user->isDoctor() || 
               ($user->isPatient() && $appointment->patient_id === $user->id);
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'sometimes|exists:users,id',
            'doctor_id' => 'sometimes|exists:users,id',
            'service_id' => 'sometimes|exists:services,id',
            'appointment_date' => 'sometimes|date|after_or_equal:today',
            'appointment_time' => 'sometimes|date_format:H:i',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'notes' => 'nullable|string|max:500',
        ];
    }
}