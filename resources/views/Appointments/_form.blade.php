{{-- Champ Patient --}}
@if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
<div class="form-group">
    <label class="form-label">{{ __('appointments.patient') }} *</label>
    <select name="patient_id" class="form-control" required id="{{ isset($appointment) ? 'edit_patient_id' : 'patient_id' }}">
        <option value="">— {{ __('appointments.select_patient') }} —</option>
        @foreach($patients as $patient)
            <option value="{{ $patient->id }}" {{ old('patient_id', $appointment?->patient_id) == $patient->id ? 'selected' : '' }}>
                {{ $patient->name }} ({{ $patient->email }})
            </option>
        @endforeach
    </select>
    @error('patient_id') <div class="form-error">{{ $message }}</div> @enderror
</div>
@else
    <input type="hidden" name="patient_id" value="{{ auth()->id() }}">
@endif

{{-- Médecin --}}
<div class="form-group">
    <label class="form-label">{{ __('appointments.doctor') }} *</label>
    <select name="doctor_id" class="form-control" required id="{{ isset($appointment) ? 'edit_doctor_id' : 'doctor_id' }}">
        <option value="">— {{ __('appointments.select_doctor') }} —</option>
        @foreach($doctors as $doctor)
            <option value="{{ $doctor->id }}" {{ old('doctor_id', $appointment?->doctor_id) == $doctor->id ? 'selected' : '' }}>
                {{ $doctor->name }} — {{ $doctor->specialty }}
            </option>
        @endforeach
    </select>
    @error('doctor_id') <div class="form-error">{{ $message }}</div> @enderror
</div>

{{-- Service --}}
<div class="form-group">
    <label class="form-label">{{ __('appointments.service') }} *</label>
    <select name="service_id" class="form-control" required id="{{ isset($appointment) ? 'edit_service_id' : 'service_id' }}">
        <option value="">— {{ __('appointments.select_service') }} —</option>
        @foreach($services as $service)
            <option value="{{ $service->id }}" {{ old('service_id', $appointment?->service_id) == $service->id ? 'selected' : '' }}>
                {{ $service->name }} ({{ $service->duration_minutes }} min)
            </option>
        @endforeach
    </select>
    @error('service_id') <div class="form-error">{{ $message }}</div> @enderror
</div>

{{-- Date & Heure --}}
<div class="form-row">
    <div class="form-group">
        <label class="form-label">{{ __('appointments.date') }} *</label>
        <input type="date" name="appointment_date" class="form-control" required
               min="{{ today()->toDateString() }}"
               value="{{ old('appointment_date', $appointment?->appointment_date?->format('Y-m-d')) }}"
               id="{{ isset($appointment) ? 'edit_appointment_date' : 'appointment_date' }}">
        @error('appointment_date') <div class="form-error">{{ $message }}</div> @enderror
    </div>
    <div class="form-group">
        <label class="form-label">{{ __('appointments.time') }} *</label>
        <input type="time" name="appointment_time" class="form-control" required
               value="{{ old('appointment_time', $appointment?->appointment_time) }}"
               id="{{ isset($appointment) ? 'edit_appointment_time' : 'appointment_time' }}">
        @error('appointment_time') <div class="form-error">{{ $message }}</div> @enderror
    </div>
</div>

{{-- Notes --}}
<div class="form-group">
    <label class="form-label">{{ __('appointments.notes') }}</label>
    <textarea name="notes" class="form-control" rows="2" placeholder="{{ __('appointments.notes_placeholder') }}"
              id="{{ isset($appointment) ? 'edit_notes' : 'notes' }}">{{ old('notes', $appointment?->notes) }}</textarea>
</div>