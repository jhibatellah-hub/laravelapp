<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:users,id',
            'doctor_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Le patient est obligatoire.',
            'doctor_id.required' => 'Le médecin est obligatoire.',
            'appointment_date.after_or_equal' => 'La date doit être aujourd\'hui ou plus tard.',
            'appointment_time.date_format' => 'L\'heure doit être au format HH:MM.',
        ];
    }
}