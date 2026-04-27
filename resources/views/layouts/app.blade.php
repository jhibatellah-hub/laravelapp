<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'JamylCabinet' }} - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
</head>
<body class="font-sans" x-data="{ sidebarOpen: false }">
    <div class="app-shell">
        @include('components.sidebar')

        <div class="app-main">
            @include('components.navbar')

            <main>
                <div class="page-head">
                    <div>
                        <h1 class="page-title">@yield('page-title', __('ui.dashboard'))</h1>
                        @hasSection('page-subtitle')
                            <p class="page-subtitle">@yield('page-subtitle')</p>
                        @endif
                    </div>

                    @hasSection('topbar-actions')
                        <div class="page-actions">
                            @yield('topbar-actions')
                        </div>
                    @endif
                </div>

                @if(session('success'))
                    <div class="panel" style="margin-bottom: 18px; color: var(--success);">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <i class="fas fa-check-circle"></i>
                            <span style="font-weight:700;">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="panel" style="margin-bottom: 18px; color: var(--danger);">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span style="font-weight:700;">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
