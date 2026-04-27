<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'MedCabinet' }} — {{ config('app.name') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }
        .glass { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); }
        .dark .glass { background: rgba(17,24,39,0.8); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased transition-colors duration-300" x-data="{ sidebarOpen: false, darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => { localStorage.setItem('darkMode', val); val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark') }); if(darkMode) document.documentElement.classList.add('dark')">
    
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        @include('components.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            @include('components.navbar')
            
            <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
                <!-- Breadcrumb -->
                @hasSection('breadcrumb')
                    <nav class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                        @yield('breadcrumb')
                    </nav>
                @endif
                
                <!-- Page Title -->
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        @yield('page-title', 'Tableau de bord')
                    </h1>
                    <div class="flex gap-3">
                        @yield('topbar-actions')
                    </div>
                </div>
                
                <!-- Alerts -->
                <div class="space-y-3 mb-6" x-data="{ show: true }">
                    @if(session('success'))
                        <div x-show="show" x-transition class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-4 py-3 rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ session('success') }}
                            </div>
                            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div x-show="show" x-transition class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ session('error') }}
                            </div>
                            <button @click="show = false" class="text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @endif
                </div>
                
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>