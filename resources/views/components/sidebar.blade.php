<aside class="app-sidebar" :class="sidebarOpen ? 'is-open' : ''">
    <div class="brand-row">
        <div class="brand-mark">
            <x-clinic-logo variant="brand" />
        </div>
        <div>
            <div class="brand-name">Petal Health</div>
            <div class="brand-meta">{{ __('ui.medical_center') }}</div>
        </div>
    </div>

    <a href="{{ route('appointments.index') }}" class="sidebar-cta">
        <i class="fas fa-plus"></i>
        <span>{{ __('ui.new_appointment') }}</span>
    </a>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
            <i class="fas fa-border-all"></i>
            <span>{{ __('ui.dashboard') }}</span>
        </a>

        <a href="{{ route('appointments.index') }}" class="sidebar-link {{ request()->routeIs('appointments.*') ? 'is-active' : '' }}">
            <i class="fas fa-calendar-days"></i>
            <span>{{ __('ui.appointments') }}</span>
            @php
                $pendingCount = \App\Models\Appointment::pending()
                    ->when(auth()->user()->isDoctor(), fn ($query) => $query->forDoctor(auth()->id()))
                    ->when(auth()->user()->isPatient(), fn ($query) => $query->forPatient(auth()->id()))
                    ->count();
            @endphp
            @if($pendingCount > 0)
                <span class="sidebar-count">{{ $pendingCount }}</span>
            @endif
        </a>
    </nav>

    <div class="sidebar-spacer"></div>

    <div class="sidebar-account">
        <div class="avatar-circle avatar-circle--brand">
            <x-clinic-logo />
        </div>
        <div>
            <div class="account-name">{{ auth()->user()->name }}</div>
            <div class="account-role">{{ __('ui.' . auth()->user()->role) }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn" title="{{ __('auth.logout') }}">
                <i class="fas fa-arrow-right-from-bracket"></i>
            </button>
        </form>
    </div>
</aside>

<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="mobile-overlay lg:hidden"></div>
