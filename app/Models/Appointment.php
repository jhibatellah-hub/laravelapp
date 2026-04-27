<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'cancellation_reason',
        'confirmed_at',
        'cancelled_at',
        'email_sent',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'appointment_time' => 'string', 
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'email_sent' => 'boolean',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function service(): BelongsTo
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

   
   public function scopeConflicting($query, $doctorId, $date, $time, $durationMinutes = 30, $excludeId = null)
    {
        $startBoundary = \Carbon\Carbon::parse($time)->subMinutes($durationMinutes)->format('H:i:s');
        $endBoundary   = \Carbon\Carbon::parse($time)->addMinutes($durationMinutes)->format('H:i:s');

        $q = $query->where('doctor_id', $doctorId)
                   ->whereDate('appointment_date', $date)
                   ->where('status', '!=', 'cancelled')
                   ->where('appointment_time', '>', $startBoundary)
                   ->where('appointment_time', '<', $endBoundary);

        if ($excludeId) {
            $q->where('id', '!=', $excludeId);
        }

        return $q;
    }
    // --- Accessors ---
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => __('appointments.status.pending'),
            'confirmed' => __('appointments.status.confirmed'),
            'cancelled' => __('appointments.status.cancelled'),
            'completed' => __('appointments.status.completed'),
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'confirmed' => 'success',
            'cancelled' => 'danger',
            'completed' => 'info',
            default => 'secondary',
        };
    }

    public function getIsPastAttribute(): bool
    {
        $dateTime = Carbon::parse("{$this->appointment_date->format('Y-m-d')} {$this->appointment_time}");
        return $dateTime->isPast();
    }

    // --- Actions ---
    public function confirm(): void
    {
        if ($this->status !== 'confirmed') {
            $this->update([
                'status' => 'confirmed', 
                'confirmed_at' => now()
            ]);
        }
    }

    public function cancel(?string $reason = null): void
    {
        if ($this->status !== 'cancelled') {
            $this->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);
        }
    }
}
