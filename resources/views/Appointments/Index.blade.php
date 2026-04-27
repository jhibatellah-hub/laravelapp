@extends('layouts.app')

@section('page-title', __('appointments.page_title'))
@section('page-subtitle', __('appointments.subtitle'))

@section('topbar-actions')
    <a href="{{ route('appointments.index', ['date' => now()->toDateString()]) }}" class="chip-btn">
        <i class="far fa-calendar"></i>
        {{ __('ui.today') }}
    </a>
    @if(auth()->user()->isAdmin() || auth()->user()->isPatient())
        <button type="button" @click="showAddModal = true" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            {{ __('appointments.book') }}
        </button>
    @endif
@endsection

@section('content')
@php
    $selectedDate = request('date') ? \Carbon\Carbon::parse(request('date')) : now();
    $monthStart = $selectedDate->copy()->startOfMonth();
    $gridStart = $monthStart->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $days = collect(range(0, 41))->map(fn ($index) => $gridStart->copy()->addDays($index));
    $prevMonth = $selectedDate->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
    $nextMonth = $selectedDate->copy()->addMonthNoOverflow()->startOfMonth()->toDateString();
@endphp

<div x-data="{
    showAddModal: false,
    showDeleteModal: false,
    showEditModal: false,
    deleteActionUrl: '',
    editActionUrl: '',
    editData: { id: '', appointment_date: '', appointment_time: '', status: 'pending', notes: '' },

    openDelete(id) {
        this.deleteActionUrl = '/appointments/' + id;
        this.showDeleteModal = true;
    },
    openEdit(appointment) {
        this.editData = {
            id: appointment.id,
            appointment_date: appointment.appointment_date.split('T')[0],
            appointment_time: appointment.appointment_time,
            status: appointment.status,
            notes: appointment.notes ?? ''
        };
        this.editActionUrl = '/appointments/' + appointment.id;
        this.showEditModal = true;
    }
}"
@open-delete.window="openDelete($event.detail)"
@open-edit.window="openEdit($event.detail)">
    <div class="appointments-layout">
        <div class="calendar-shell">
            <div class="panel">
                <div class="calendar-head">
                    <div class="calendar-label">{{ $selectedDate->isoFormat('MMMM YYYY') }}</div>
                    <div class="calendar-nav">
                        <a href="{{ route('appointments.index', array_filter(['date' => $prevMonth, 'status' => request('status'), 'doctor_id' => request('doctor_id')])) }}">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('appointments.index', array_filter(['date' => $nextMonth, 'status' => request('status'), 'doctor_id' => request('doctor_id')])) }}">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>

                <div class="calendar-grid" style="margin-bottom: 12px;">
                    @foreach(['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'] as $weekday)
                        <div class="calendar-weekday">{{ $weekday }}</div>
                    @endforeach
                </div>

                <div class="calendar-grid">
                    @foreach($days as $day)
                        @php
                            $isCurrentMonth = $day->month === $selectedDate->month;
                            $isSelected = $day->isSameDay($selectedDate);
                            $isToday = $day->isSameDay(now());
                        @endphp
                        <a href="{{ route('appointments.index', array_filter(['date' => $day->toDateString(), 'status' => request('status'), 'doctor_id' => request('doctor_id')])) }}"
                           class="calendar-day {{ $isCurrentMonth ? '' : 'is-muted' }} {{ $isSelected ? 'is-selected' : '' }} {{ $isToday && !$isSelected ? 'is-today' : '' }}">
                            {{ $day->day }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="panel">
                <div class="panel-heading" style="margin-bottom: 10px;">
                    <div>
                        <div class="panel-title">{{ __('appointments.filters_title') }}</div>
                    </div>
                </div>

                <form method="GET" action="{{ route('appointments.index') }}" class="filter-stack">
                    <input type="hidden" id="dateFilter" name="date" value="{{ request('date', $selectedDate->toDateString()) }}">

                    <div>
                        <label class="field-label" for="statusFilter">{{ __('appointments.filter_status') }}</label>
                        <select id="statusFilter" name="status" class="field-select">
                            <option value="">{{ __('appointments.all_statuses') }}</option>
                            <option value="pending" @selected(request('status') === 'pending')>{{ __('appointments.status.pending') }}</option>
                            <option value="confirmed" @selected(request('status') === 'confirmed')>{{ __('appointments.status.confirmed') }}</option>
                            <option value="completed" @selected(request('status') === 'completed')>{{ __('appointments.status.completed') }}</option>
                            <option value="cancelled" @selected(request('status') === 'cancelled')>{{ __('appointments.status.cancelled') }}</option>
                        </select>
                    </div>

                    @if(auth()->user()->isAdmin() || auth()->user()->isPatient())
                        <div>
                            <label class="field-label" for="doctorFilter">{{ __('appointments.filter_doctor') }}</label>
                            <select id="doctorFilter" name="doctor_id" class="field-select">
                                <option value="">{{ __('appointments.all_doctors') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" @selected((string) request('doctor_id') === (string) $doctor->id)>{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-secondary" style="width: 100%;">{{ __('appointments.confirm') }}</button>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-heading">
                <div>
                    <div class="panel-title">{{ __('appointments.schedule_for', ['date' => $selectedDate->isoFormat('MMM D')]) }}</div>
                    <div class="panel-subtitle">{{ __('appointments.records_caption', ['count' => $appointments->total()]) }}</div>
                </div>
            </div>

            <div class="appointments-toolbar">
                <div class="inline-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="{{ __('appointments.search_placeholder') }}">
                </div>
                <span id="resultCount" class="cell-subtitle">{{ $appointments->count() }} / {{ $appointments->total() }}</span>
            </div>

            <table class="data-table appointments-table">
                <thead>
                    <tr>
                        <th>{{ __('appointments.time') }}</th>
                        <th>{{ __('appointments.patient') }}</th>
                        <th>{{ __('appointments.doctor') }}</th>
                        <th>{{ __('appointments.status_label') }}</th>
                        <th>{{ __('appointments.actions') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>
                                <div class="cell-title">{{ $appointment->appointment_time }}</div>
                                <div class="cell-subtitle">{{ $appointment->service->duration_minutes ?? 30 }} min</div>
                            </td>
                            <td>
                                <div class="person-cell">
                                    <div class="person-avatar">{{ $appointment->patient->initials }}</div>
                                    <div>
                                        <div class="cell-title">{{ $appointment->patient->name }}</div>
                                        <div class="cell-subtitle">{{ $appointment->service->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="cell-title">{{ $appointment->doctor->name }}</div>
                                <div class="cell-subtitle">{{ $appointment->doctor->specialty ?? __('ui.medical_center') }}</div>
                            </td>
                            <td>
                                <span class="status-pill is-{{ $appointment->status }}">{{ $appointment->status_label }}</span>
                            </td>
                            <td>
                                <div style="display:flex; gap:10px; justify-content:flex-end;">
                                    @if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
                                        <button type="button" class="icon-btn" @click='openEdit(@json($appointment))' title="{{ __('appointments.edit') }}">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                    @endif
                                    @if(auth()->user()->isAdmin() || (auth()->user()->isPatient() && $appointment->status === 'pending'))
                                        <button type="button" class="icon-btn" @click="openDelete({{ $appointment->id }})" title="{{ __('appointments.cancel') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="cell-subtitle">{{ __('appointments.empty') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($appointments->hasPages())
                <div class="pagination-shell" style="margin-top: 18px;">
                    {{ $appointments->links() }}
                </div>
            @endif
        </div>
    </div>

    @if(auth()->user()->isAdmin() || auth()->user()->isPatient())
        <div x-show="showAddModal" x-cloak class="modal-backdrop">
            <div class="modal-panel" @click.away="showAddModal = false">
                <div class="panel-heading">
                    <div>
                        <div class="modal-title">{{ __('appointments.create_title') }}</div>
                        <div class="modal-copy">{{ __('appointments.subtitle') }}</div>
                    </div>
                    <button type="button" class="icon-btn" @click="showAddModal = false"><i class="fas fa-xmark"></i></button>
                </div>

                <form action="{{ route('appointments.store') }}" method="POST" class="form-grid">
                    @csrf

                    @if(!auth()->user()->isPatient())
                        <div>
                            <label class="field-label">{{ __('appointments.patient') }}</label>
                            <select name="patient_id" class="field-select" required>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="patient_id" value="{{ auth()->id() }}">
                    @endif

                    <div class="form-grid two">
                        <div>
                            <label class="field-label">{{ __('appointments.doctor') }}</label>
                            <select name="doctor_id" class="field-select" required>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="field-label">{{ __('appointments.service') }}</label>
                            <select name="service_id" class="field-select" required>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->price }} {{ __('appointments.price_suffix') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-grid two">
                        <div>
                            <label class="field-label">{{ __('appointments.date') }}</label>
                            <input type="date" name="appointment_date" class="field-control" min="{{ now()->toDateString() }}" value="{{ request('date', now()->toDateString()) }}" required>
                        </div>
                        <div>
                            <label class="field-label">{{ __('appointments.time') }}</label>
                            <input type="time" name="appointment_time" class="field-control" required>
                        </div>
                    </div>

                    <div>
                        <label class="field-label">{{ __('appointments.notes') }}</label>
                        <textarea name="notes" class="field-textarea"></textarea>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" @click="showAddModal = false">{{ __('appointments.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('appointments.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div x-show="showEditModal" x-cloak class="modal-backdrop">
        <div class="modal-panel" @click.away="showEditModal = false">
            <div class="panel-heading">
                <div>
                    <div class="modal-title">{{ __('appointments.edit_title') }}</div>
                    <div class="modal-copy">{{ __('appointments.schedule_for', ['date' => $selectedDate->isoFormat('MMM D')]) }}</div>
                </div>
                <button type="button" class="icon-btn" @click="showEditModal = false"><i class="fas fa-xmark"></i></button>
            </div>

            <form :action="editActionUrl" method="POST" class="form-grid">
                @csrf
                @method('PUT')

                <div class="form-grid two">
                    <div>
                        <label class="field-label">{{ __('appointments.date') }}</label>
                        <input type="date" name="appointment_date" class="field-control" x-model="editData.appointment_date" min="{{ now()->toDateString() }}">
                    </div>
                    <div>
                        <label class="field-label">{{ __('appointments.time') }}</label>
                        <input type="time" name="appointment_time" class="field-control" x-model="editData.appointment_time">
                    </div>
                </div>

                <div>
                    <label class="field-label">{{ __('appointments.status_label') }}</label>
                    <select name="status" class="field-select" x-model="editData.status">
                        <option value="pending">{{ __('appointments.status.pending') }}</option>
                        <option value="confirmed">{{ __('appointments.status.confirmed') }}</option>
                        <option value="completed">{{ __('appointments.status.completed') }}</option>
                        <option value="cancelled">{{ __('appointments.status.cancelled') }}</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">{{ __('appointments.notes') }}</label>
                    <textarea name="notes" class="field-textarea" x-model="editData.notes"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" @click="showEditModal = false">{{ __('appointments.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('appointments.update') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showDeleteModal" x-cloak class="modal-backdrop">
        <div class="modal-panel is-narrow" @click.away="showDeleteModal = false">
            <div class="modal-title">{{ __('appointments.delete_title_delete') }}</div>
            <div class="modal-copy">{{ __('appointments.delete_message') }}</div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">{{ __('appointments.close') }}</button>
                <form :action="deleteActionUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-primary">{{ __('appointments.confirm') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    const isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
    const isDoctor = {{ auth()->user()->isDoctor() ? 'true' : 'false' }};
    const isPatient = {{ auth()->user()->isPatient() ? 'true' : 'false' }};
    const searchInput = document.getElementById('searchInput');
    const resultCount = document.getElementById('resultCount');
    const statusFilter = document.getElementById('statusFilter');
    const doctorFilter = document.getElementById('doctorFilter');
    const dateFilter = document.getElementById('dateFilter');
    let searchTimeout;

    function statusClass(status) {
        return {
            pending: 'is-pending',
            confirmed: 'is-confirmed',
            completed: 'is-completed',
            cancelled: 'is-cancelled'
        }[status] || 'is-pending';
    }

    function doSearch() {
        axios.get('{{ route('appointments.search') }}', {
            params: {
                q: searchInput.value.trim(),
                status: statusFilter ? statusFilter.value : '',
                doctor_id: doctorFilter ? doctorFilter.value : '',
                date: dateFilter ? dateFilter.value : ''
            }
        }).then(function (response) {
            const rows = response.data;
            const tbody = document.getElementById('tableBody');
            resultCount.textContent = rows.length;

            if (!rows.length) {
                tbody.innerHTML = `<tr><td colspan="5"><div class="cell-subtitle">{{ __('appointments.empty') }}</div></td></tr>`;
                return;
            }

            tbody.innerHTML = rows.map(function (row) {
                const initials = row.patient_name.split(' ').map(part => part.charAt(0)).join('').slice(0, 2).toUpperCase();
                let actions = '';

                if (isAdmin || isDoctor) {
                    actions += `<button type="button" class="icon-btn" title="{{ __('appointments.edit') }}" data-appointment="${encodeURIComponent(JSON.stringify(row))}" onclick='window.dispatchEvent(new CustomEvent("open-edit", { detail: JSON.parse(decodeURIComponent(this.dataset.appointment)) }))'><i class="fas fa-pen"></i></button>`;
                }

                if (isAdmin || (isPatient && row.status === 'pending')) {
                    actions += `<button type="button" class="icon-btn" title="{{ __('appointments.cancel') }}" onclick='window.dispatchEvent(new CustomEvent("open-delete", { detail: ${row.id} }))'><i class="fas fa-trash"></i></button>`;
                }

                return `
                    <tr>
                        <td>
                            <div class="cell-title">${row.appointment_time}</div>
                            <div class="cell-subtitle">${row.duration_minutes} min</div>
                        </td>
                        <td>
                            <div class="person-cell">
                                <div class="person-avatar">${initials}</div>
                                <div>
                                    <div class="cell-title">${row.patient_name}</div>
                                    <div class="cell-subtitle">${row.service_name}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="cell-title">${row.doctor_name}</div>
                            <div class="cell-subtitle">${row.doctor_specialty || '{{ __('ui.medical_center') }}'}</div>
                        </td>
                        <td>
                            <span class="status-pill ${statusClass(row.status)}">${row.status_label}</span>
                        </td>
                        <td>
                            <div style="display:flex; gap:10px; justify-content:flex-end;">${actions}</div>
                        </td>
                    </tr>
                `;
            }).join('');
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(doSearch, 250);
        });
    }

    [statusFilter, doctorFilter, dateFilter].filter(Boolean).forEach(function (element) {
        element.addEventListener('change', doSearch);
    });
</script>
@endpush
@endsection
