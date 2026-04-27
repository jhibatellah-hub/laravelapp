@extends('layouts.app')

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

{{-- Nwerriw hadchi GHIR l'Admin wla Tbib --}}
@if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
    
    {{-- Stats Globale --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--primary-lt)"><i class="fas fa-calendar-alt" style="color:var(--primary-mid)"></i></div>
            <div class="stat-value">{{ $stats['total_appointments'] ?? 0 }}</div>
            <div class="stat-label">{{ __('dashboard.total_appointments') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--warning-lt)"><i class="fas fa-clock" style="color:var(--warning)"></i></div>
            <div class="stat-value">{{ $stats['pending_appointments'] ?? 0 }}</div>
            <div class="stat-label">{{ __('dashboard.pending') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--success-lt)"><i class="fas fa-check-circle" style="color:var(--success)"></i></div>
            <div class="stat-value">{{ $stats['confirmed_appointments'] ?? 0 }}</div>
            <div class="stat-label">{{ __('dashboard.confirmed') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#EAF3DE"><i class="fas fa-user-friends" style="color:var(--success)"></i></div>
            <div class="stat-value">{{ $stats['total_patients'] ?? 0 }}</div>
            <div class="stat-label">{{ __('dashboard.patients') }}</div>
        </div>
    </div>

@else
    {{-- Stats dyal l'Patient --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--primary-lt)"><i class="fas fa-calendar-alt" style="color:var(--primary-mid)"></i></div>
            <div class="stat-value">{{ $stats['my_total_appointments'] ?? 0 }}</div>
            <div class="stat-label">Mes Rendez-vous</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--warning-lt)"><i class="fas fa-clock" style="color:var(--warning)"></i></div>
            <div class="stat-value">{{ $stats['my_pending'] ?? 0 }}</div>
            <div class="stat-label">En attente</div>
        </div>
    </div>
@endif


<div class="two-cols">
    {{-- Prochains RDV (Kaybanou lkoulchi) --}}
    <div class="card">
        <div class="section-title">
            <i class="fas fa-calendar-day"></i> {{ __('dashboard.upcoming') }}
        </div>
        @forelse($upcomingAppointments as $rdv)
            <div class="rdv-item">
                <div class="rdv-avatar">{{ $rdv->patient->initials ?? substr($rdv->patient->name, 0, 2) }}</div>
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
    </div>

    {{-- Graphique 7 jours (Kayban ghir l'Admin w Tbib) --}}
    @if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
        <div class="card">
            <div class="section-title">
                <i class="fas fa-chart-bar"></i> {{ __('dashboard.last_7_days') }}
            </div>
            @php 
                $chartDataArray = $chartData ?? [];
                $maxVal = count($chartDataArray) > 0 ? max(array_column($chartDataArray, 'count')) : 1; 
                $maxVal = $maxVal ?: 1; // Bach man9esmouch 3la zero
            @endphp
            <div class="chart-bar-wrap">
                @foreach($chartDataArray as $day)
                <div class="chart-bar-col">
                    <div class="chart-bar" style="height:{{ max(($day['count'] / $maxVal) * 100, 5) }}%" title="{{ $day['count'] }} RDV"></div>
                    <span class="chart-label">{{ $day['date'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
