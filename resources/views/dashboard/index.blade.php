@extends('layouts.app')

@section('page-title', __('dashboard.page_title', ['name' => auth()->user()->name]))
@section('page-subtitle', __('dashboard.subtitle'))

@section('topbar-actions')
    <span class="chip-btn">
        <i class="far fa-calendar"></i>
        {{ now()->isoFormat('ddd, D MMM') }}
    </span>
    <a href="{{ route('appointments.index') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        {{ __('ui.new_appointment') }}
    </a>
@endsection

@section('content')
    <div class="content-stack">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-head">
                    <div class="stat-icon is-accent"><i class="fas fa-user-group"></i></div>
                    <span class="stat-trend is-positive"><i class="fas fa-arrow-trend-up"></i> +12%</span>
                </div>
                <div class="stat-value">{{ auth()->user()->isPatient() ? ($stats['my_total_appointments'] ?? 0) : ($stats['total_patients'] ?? 0) }}</div>
                <div class="stat-meta">{{ auth()->user()->isPatient() ? __('dashboard.my_appointments') : __('dashboard.patients') }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-head">
                    <div class="stat-icon is-blue"><i class="fas fa-calendar-check"></i></div>
                    <span class="stat-trend is-positive"><i class="fas fa-arrow-trend-up"></i> +4</span>
                </div>
                <div class="stat-value">{{ auth()->user()->isPatient() ? ($stats['my_pending'] ?? 0) : ($stats['total_appointments'] ?? 0) }}</div>
                <div class="stat-meta">{{ auth()->user()->isPatient() ? __('dashboard.pending') : __('dashboard.total_appointments') }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-head">
                    <div class="stat-icon is-teal"><i class="fas fa-user-doctor"></i></div>
                    <span class="stat-trend is-positive"><i class="fas fa-plus"></i> 8 / 12</span>
                </div>
                <div class="stat-value">{{ auth()->user()->isPatient() ? ($stats['my_confirmed'] ?? 0) : ($stats['confirmed_appointments'] ?? 0) }}</div>
                <div class="stat-meta">{{ __('dashboard.confirmed') }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-head">
                    <div class="stat-icon is-neutral"><i class="fas fa-notes-medical"></i></div>
                    <span class="stat-trend is-positive"><i class="fas fa-arrow-trend-up"></i> +8%</span>
                </div>
                <div class="stat-value">{{ count($upcomingAppointments) }}</div>
                <div class="stat-meta">{{ __('dashboard.upcoming') }}</div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="panel">
                <div class="panel-heading">
                    <div>
                        <div class="panel-title">{{ __('dashboard.recent_title') }}</div>
                        <div class="panel-subtitle">{{ __('dashboard.recent_subtitle') }}</div>
                    </div>
                    <a href="{{ route('appointments.index') }}" class="muted-link">{{ __('dashboard.see_all') }}</a>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('appointments.patient') }}</th>
                            <th>{{ __('appointments.doctor') }}</th>
                            <th>{{ __('appointments.date_time') }}</th>
                            <th>{{ __('appointments.status_label') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingAppointments as $appointment)
                            <tr>
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
                                    <div class="cell-title">{{ $appointment->appointment_date->format('M d, H:i') }}</div>
                                    <div class="cell-subtitle">{{ $appointment->appointment_time }}</div>
                                </td>
                                <td>
                                    <span class="status-pill is-{{ $appointment->status }}">
                                        {{ $appointment->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="cell-subtitle">{{ __('dashboard.no_upcoming') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="content-stack">
                <div class="panel">
                    <div class="panel-heading">
                        <div>
                            <div class="panel-title">{{ __('dashboard.upcoming_panel') }}</div>
                            <div class="panel-subtitle">{{ __('dashboard.upcoming_panel_subtitle') }}</div>
                        </div>
                    </div>

                    <div class="mini-feed">
                        @forelse($upcomingAppointments as $appointment)
                            <div class="mini-feed-item">
                                <div class="feed-time">
                                    {{ $appointment->appointment_time }}
                                    <span class="feed-duration">{{ $appointment->service->duration_minutes }} min</span>
                                </div>
                                <div style="flex: 1;">
                                    <div class="cell-title">{{ $appointment->patient->name }}</div>
                                    <div class="cell-subtitle">{{ $appointment->doctor->name }} · {{ $appointment->service->name }}</div>
                                </div>
                                <span class="status-pill is-{{ $appointment->status }}">{{ $appointment->status_label }}</span>
                            </div>
                        @empty
                            <div class="cell-subtitle">{{ __('dashboard.no_upcoming') }}</div>
                        @endforelse
                    </div>
                </div>

                @if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                <div class="panel-title">{{ __('dashboard.last_7_days') }}</div>
                                <div class="panel-subtitle">{{ __('dashboard.appointments_per_day') }}</div>
                            </div>
                        </div>

                        @php
                            $chartPoints = $chartData ?? [];
                            $chartMax = max(1, collect($chartPoints)->max('count'));
                        @endphp

                        <div style="display:flex; align-items:flex-end; gap:12px; height:180px;">
                            @foreach($chartPoints as $point)
                                <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:10px; height:100%;">
                                    <span style="font-size:0.78rem; color:var(--text-soft);">{{ $point['count'] }}</span>
                                    <div style="width:100%; border-radius:999px; background:linear-gradient(180deg, rgba(195,62,134,0.94), rgba(237,139,183,0.9)); height:{{ max(12, ($point['count'] / $chartMax) * 120) }}px;"></div>
                                    <span style="font-size:0.75rem; font-weight:700; color:#9d97aa;">{{ $point['date'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
