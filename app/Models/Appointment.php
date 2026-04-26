<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id', 'doctor_id', 'service_id',
        'appointment_date', 'appointment_time', 'status',
        'notes', 'cancellation_reason', 'email_sent',
        'confirmed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'appointment_time' => 'datetime:H:i', 
            'confirmed_at'     => 'datetime',
            'cancelled_at'     => 'datetime',
            'email_sent'       => 'boolean',
        ];
    }

    // --- Relations ---
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // --- Scopes ---
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', today())
                     ->whereIn('status', ['pending', 'confirmed']);
    }

    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * ZEDT HADA: Scope bach n9elbou wach kayn conflit dyal l'wa9t.
     * Daba f l'controller t9der dir: Appointment::conflicting($doctor, $date, $time)->exists();
     */
    public function scopeConflicting($query, $doctorId, $date, $time, $excludeId = null)
    {
        $query->where('doctor_id', $doctorId)
              ->whereDate('appointment_date', $date)
              // Kan9elbou 3la nafs l'waxt (awla t9der tzid logic dial chhal mof l'consultation)
              ->whereTime('appointment_time', Carbon::parse($time)->format('H:i:s'))
              ->whereNotIn('status', ['cancelled']);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
    }

    // --- Helpers (Accessors) ---
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'   => __('appointments.status.pending'),
            'confirmed' => __('appointments.status.confirmed'),
            'cancelled' => __('appointments.status.cancelled'),
            'completed' => __('appointments.status.completed'),
            default     => ucfirst($this->status), // Fallback mzyan
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'   => 'warning',
            'confirmed' => 'success',
            'cancelled' => 'danger',
            'completed' => 'info',
            default     => 'secondary',
        };
    }

    /**
     * ZEDT HADA: Helper kaygolik wach l'rendez-vous daz waqto wla mzal (True/False)
     * T9der tkhdem bih f Blade: @if($appointment->is_past) ... @endif
     */
    public function getIsPastAttribute(): bool
    {
        // Kan-jme3ou date w l'heure bach n-comparéwhom m3a l'wa9t d daba
        $appointmentDateTime = Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time->format('H:i'));
        return $appointmentDateTime->isPast();
    }

    // --- Actions ---
    public function confirm(): void
    {
        // Ta7sin sghir: mandirou update hta n-vérifiw wach machi déja confirmed
        if ($this->status !== 'confirmed') {
            $this->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        }
    }

    public function cancel(string $reason = null): void
    {
        if ($this->status !== 'cancelled') {
            $this->update([
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancellation_reason' => $reason,
            ]);
        }
    }
}