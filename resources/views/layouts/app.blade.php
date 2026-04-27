<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'MedCabinet' }} — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=Amiri:wght@400;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary:     #0C447C;
            --primary-mid: #185FA5;
            --primary-lt:  #E6F1FB;
            --primary-b:   #B5D4F4;
            --success:     #3B6D11;
            --success-lt:  #EAF3DE;
            --warning:     #854F0B;
            --warning-lt:  #FAEEDA;
            --danger:      #A32D2D;
            --danger-lt:   #FCEBEB;
            --text:        #1a1a2e;
            --text-muted:  #6b7280;
            --border:      #e5e7eb;
            --bg:          #f8fafc;
            --sidebar-w:   240px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.6;
        }
        body[dir="rtl"] { font-family: 'Amiri', 'DM Sans', sans-serif; }

        .layout { display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--primary);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: transform 0.3s;
        }
        [dir="rtl"] .sidebar { left: auto; right: 0; }

        .sidebar-brand {
            padding: 20px 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .brand-icon {
            width: 36px; height: 36px;
            background: var(--primary-mid);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 10px;
        }
        .brand-icon i { color: white; font-size: 17px; }
        .brand-name { font-size: 14px; font-weight: 600; color: #fff; }
        .brand-sub  { font-size: 11px; color: rgba(181,212,244,0.7); margin-top: 2px; }

        .sidebar-nav { padding: 12px 8px; flex: 1; overflow-y: auto; }
        .nav-section {
            font-size: 10px; letter-spacing: 1.2px;
            color: rgba(181,212,244,0.45);
            padding: 12px 10px 5px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px;
            color: rgba(181,212,244,0.75);
            font-size: 13px; font-weight: 400;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            margin-bottom: 2px;
        }
        .nav-item i { width: 16px; text-align: center; font-size: 13px; }
        .nav-item:hover { background: rgba(255,255,255,0.08); color: white; }
        .nav-item.active { background: var(--primary-mid); color: white; font-weight: 500; }
        .nav-badge {
            margin-left: auto;
            background: #E24B4A; color: white;
            font-size: 10px; border-radius: 10px;
            padding: 1px 7px;
        }
        [dir="rtl"] .nav-badge { margin-left: 0; margin-right: auto; }

        .sidebar-footer {
            padding: 14px 18px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-user { display: flex; align-items: center; gap: 10px; }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--primary-mid);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 600; color: white;
            flex-shrink: 0;
        }
        .user-name { font-size: 12px; color: #fff; font-weight: 500; }
        .user-role { font-size: 10px; color: rgba(181,212,244,0.6); }

        .lang-switcher {
            display: flex; gap: 4px; margin-top: 10px;
        }
        .lang-btn {
            font-size: 11px; padding: 3px 8px; border-radius: 5px;
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(181,212,244,0.7);
            text-decoration: none;
            transition: background 0.15s;
        }
        .lang-btn.active { background: rgba(255,255,255,0.15); color: white; }

        /* ── Main ── */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        [dir="rtl"] .main { margin-left: 0; margin-right: var(--sidebar-w); }

        /* ── Topbar ── */
        .topbar {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 56px;
            display: flex; align-items: center; gap: 14px;
            position: sticky; top: 0; z-index: 50;
        }
        .topbar-title { font-size: 16px; font-weight: 600; color: var(--text); }
        .topbar-breadcrumb { font-size: 12px; color: var(--text-muted); }
        .topbar-spacer { flex: 1; }
        .topbar-actions { display: flex; align-items: center; gap: 10px; }

        /* ── Content ── */
        .content { padding: 24px 28px; flex: 1; }

        /* ── Components ── */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 16px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            border: none; cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
            font-family: inherit;
        }
        .btn-primary { background: var(--primary-mid); color: white; }
        .btn-primary:hover { background: var(--primary); }
        .btn-outline { background: #fff; color: var(--text); border: 1px solid var(--border); }
        .btn-outline:hover { background: var(--bg); }
        .btn-danger { background: var(--danger-lt); color: var(--danger); border: 1px solid #F7C1C1; }
        .btn-danger:hover { background: #F7C1C1; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }

        .badge {
            display: inline-flex; align-items: center;
            padding: 3px 9px; border-radius: 5px;
            font-size: 11px; font-weight: 500;
        }
        .badge-success { background: var(--success-lt); color: var(--success); }
        .badge-warning { background: var(--warning-lt); color: var(--warning); }
        .badge-danger  { background: var(--danger-lt);  color: var(--danger); }
        .badge-info    { background: var(--primary-lt); color: var(--primary-mid); }
        .badge-secondary { background: #f3f4f6; color: #6b7280; }

        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        }

        /* ── Alerts ── */
        .alert {
            padding: 12px 16px; border-radius: 8px;
            margin-bottom: 16px; font-size: 13px;
            display: flex; align-items: center; gap: 9px;
        }
        .alert-success { background: var(--success-lt); color: var(--success); border: 1px solid #C0DD97; }
        .alert-danger  { background: var(--danger-lt);  color: var(--danger);  border: 1px solid #F7C1C1; }

        /* ── Modal ── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(4,44,83,0.5);
            z-index: 1000;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .modal-box {
            background: #fff;
            border-radius: 14px;
            width: 100%; max-width: 500px;
            max-height: 90vh; overflow-y: auto;
            padding: 24px;
            animation: modalIn 0.2s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(-12px) scale(0.97); }
            to   { opacity: 1; transform: none; }
        }
        .modal-title {
            font-size: 16px; font-weight: 600;
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 18px;
        }
        .modal-close {
            background: none; border: none; cursor: pointer;
            color: var(--text-muted); font-size: 18px;
            padding: 4px; line-height: 1;
        }

        /* ── Forms ── */
        .form-group { margin-bottom: 14px; }
        .form-label { display: block; font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 5px; }
        .form-control {
            width: 100%; padding: 9px 12px;
            border: 1px solid var(--border); border-radius: 8px;
            font-size: 13px; color: var(--text);
            font-family: inherit;
            transition: border-color 0.15s;
            background: #fff;
        }
        .form-control:focus { outline: none; border-color: var(--primary-mid); box-shadow: 0 0 0 3px rgba(24,95,165,0.1); }
        .form-error { font-size: 11px; color: var(--danger); margin-top: 4px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        /* ── Table ── */
        .table-responsive { overflow-x: auto; border-radius: 10px; border: 1px solid var(--border); }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f9fafb; }
        th {
            padding: 10px 14px; text-align: left;
            font-size: 11px; font-weight: 600;
            color: var(--text-muted); letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }
        [dir="rtl"] th { text-align: right; }
        td {
            padding: 12px 14px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px; vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafafa; }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            [dir="rtl"] .sidebar { transform: translateX(100%); }
            .sidebar.open { transform: none; }
            .main { margin-left: 0; }
            [dir="rtl"] .main { margin-right: 0; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>

    @stack('styles')
</head>
<body>
<div class="layout">
    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon"><i class="fas fa-notes-medical"></i></div>
            <div class="brand-name">JamylCabinet</div>
            <div class="brand-sub">{{ __('app.subtitle') }}</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">{{ __('nav.main') }}</div>
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> {{ __('nav.dashboard') }}
            </a>
            <a href="{{ route('appointments.index') }}" class="nav-item {{ request()->routeIs('appointments*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check"></i> {{ __('nav.appointments') }}
                @php $pending = \App\Models\Appointment::pending()->count(); @endphp
                @if($pending > 0)
                    <span class="nav-badge">{{ $pending }}</span>
                @endif
            </a>
            @if(auth()->user()->isAdmin() || auth()->user()->isDoctor())
            <a href="#" class="nav-item">
                <i class="fas fa-user-injured"></i> {{ __('nav.patients') }}
            </a>
            @endif
            <a href="#" class="nav-item">
                <i class="fas fa-stethoscope"></i> {{ __('nav.services') }}
            </a>

            <div class="nav-section">{{ __('nav.admin') }}</div>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-bar"></i> {{ __('nav.reports') }}
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i> {{ __('nav.settings') }}
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">{{ auth()->user()->initials }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ __(auth()->user()->role) }}</div>
                </div>
            </div>
            <div class="lang-switcher">
    <a href="{{ route('locale', 'fr') }}" class="lang-btn {{ app()->getLocale() === 'fr' ? 'active' : '' }}">FR</a>
    <a href="{{ route('locale', 'en') }}" class="lang-btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">EN</a>
</div>
            <form method="POST" action="{{ route('logout') }}" style="margin-top:8px">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="width:100%; justify-content:center; color:rgba(181,212,244,0.7); background:transparent; border-color:rgba(255,255,255,0.15);">
                    <i class="fas fa-sign-out-alt"></i> {{ __('auth.logout') }}
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="main">
        <header class="topbar">
            <button class="btn btn-outline btn-sm" id="sidebarToggle" style="display:none">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <div class="topbar-title">@yield('page-title', 'Tableau de bord')</div>
                <div class="topbar-breadcrumb">@yield('breadcrumb')</div>
            </div>
            <div class="topbar-spacer"></div>
            <div class="topbar-actions">
                @yield('topbar-actions')
            </div>
        </header>

        <main class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('open');
});
</script>
@stack('scripts')
</body>
</html>