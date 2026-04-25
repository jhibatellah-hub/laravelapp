@extends('layouts.app')

@section('page-title', __('nav.dashboard'))
@section('breadcrumb', __('app.welcome') . ', ' . auth()->user()->name)

@section('topbar-actions')
    <a href="{{ route('appointments.index') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> {{ __('appointments.new') }}
    </a>
@endsection

@section('content')
<style>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 18px; }
.stat-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }
.stat-icon i { font-size: 16px; }
.stat-value { font-size: 26px; font-weight: 700; color: var(--text); line-height: 1; }
.stat-label { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
.stat-trend { font-size: 11px; margin-top: 6px; display: flex; align-items: center; gap: 4px; }

.section-title { font-size: 15px; font-weight: 600; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.section-title i { color: var(--primary-mid); }

.two-cols { display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; }
@media (max-width: 900px) { .two-cols { grid-template-columns: 1fr; } }

.rdv-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
.rdv-item:last-child { border-bottom: none; }
.rdv-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--primary-lt); color: var(--primary-mid); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; flex-shrink: 0; }
.rdv-info { flex: 1; min-width: 0; }
.rdv-name { font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rdv-detail { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
.rdv-time { font-size: 13px; font-weight: 500; color: var(--primary-mid); flex-shrink: 0; }

.chart-bar-wrap { display: flex; align-items: flex-end; gap: 8px; height: 120px; padding: 0 0 8px; }
.chart-bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end; }
.chart-bar { width: 100%; background: var(--primary-lt); border-radius: 4px 4px 0 0; transition: background 0.2s; min-height: 4px; }
.chart-bar:hover { background: var(--primary-mid); }
.chart-label { font-size: 10px; color: var(--text-muted); }
</style>

{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--primary-lt)">
            <i class="fas fa-calendar-alt" style="color:var(--primary-mid)"></i>
        </div>
        <div class="stat-value">{{ $stats['total_appointments'] }}</div>
        <div class="stat-label">{{ __('dashboard.total_appointments') }}</div>
        <div class="stat-trend" style="color:var(--success)">
            <i class="fas fa-arrow-up"></i> {{ __('dashboard.this_month') }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--warning-lt)">
            <i class="fas fa-clock" style="color:var(--warning)"></i>
        </div>
        <div class="stat-value">{{ $stats['pending_appointments'] }}</div>
        <div class="stat-label">{{ __('dashboard.pending') }}</div>
        <div class="stat-trend" style="color:var(--warning)">
            <i class="fas fa-exclamation-circle"></i> {{ __('dashboard.to_confirm') }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--success-lt)">
            <i class="fas fa-check-circle" style="color:var(--success)"></i>
        </div>
        <div class="stat-value">{{ $stats['confirmed_appointments'] }}</div>
        <div class="stat-label">{{ __('dashboard.confirmed') }}</div>
        <div class="stat-trend" style="color:var(--success)">
            <i class="fas fa-arrow-up"></i> {{ __('dashboard.good') }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#EAF3DE">
            <i class="fas fa-user-friends" style="color:var(--success)"></i>
        </div>
        <div class="stat-value">{{ $stats['total_patients'] }}</div>
        <div class="stat-label">{{ __('dashboard.patients') }}</div>
        <div class="stat-trend" style="color:var(--success)">
            <i class="fas fa-plus"></i> {{ __('dashboard.active') }}
        </div>
    </div>
</div>

<div class="two-cols">
    {{-- Prochains RDV --}}
    <div class="card">
        <div class="section-title">
            <i class="fas fa-calendar-day"></i> {{ __('dashboard.upcoming') }}
        </div>
        @forelse($upcomingAppointments as $rdv)
            <div class="rdv-item">
                <div class="rdv-avatar">{{ $rdv->patient->initials }}</div>
                <div class="rdv-info">
                    <div class="rdv-name">{{ $rdv->patient->name }}</div>
                    <div class="rdv-detail">
                        {{ $rdv->doctor->name }} · {{ $rdv->service->name }}
                        <span class="badge badge-{{ $rdv->status_color }}" style="margin-left:6px">{{ $rdv->status_label }}</span>
                    </div>
                </div>
                <div class="rdv-time">
                    {{ $rdv->appointment_date->format('d/m') }}<br>
                    <span style="font-size:11px;color:var(--text-muted)">{{ $rdv->appointment_time }}</span>
                </div>
            </div>
        @empty
            <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:20px 0">
                {{ __('dashboard.no_upcoming') }}
            </p>
        @endforelse
        <div style="margin-top:14px">
            <a href="{{ route('appointments.index') }}" class="btn btn-outline btn-sm">
                {{ __('dashboard.see_all') }} <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>

    {{-- Graphique 7 jours --}}
    <div class="card">
        <div class="section-title">
            <i class="fas fa-chart-bar"></i> {{ __('dashboard.last_7_days') }}
        </div>
            @php $maxVal = max(array_column($chartData, 'count')) ?: 1; @endphp
        <div class="chart-bar-wrap">
            @foreach($chartData as $day)
            <div class="chart-bar-col">
                <div class="chart-bar" style="height:{{ max(($day['count'] / $maxVal) * 100, 5) }}%" title="{{ $day['count'] }} RDV"></div>
                <span class="chart-label">{{ $day['date'] }}</span>
            </div>
            @endforeach
        </div>
        <div style="margin-top:12px;font-size:12px;color:var(--text-muted);text-align:center">
            {{ __('dashboard.appointments_per_day') }}
        </div>
    </div>
</div>
@endsection