<header class="app-topbar">
    <div class="topbar-left">
        <button @click="sidebarOpen = !sidebarOpen" class="topbar-mobile-trigger">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-search">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="{{ __('ui.search_placeholder') }}">
        </div>
    </div>

    <div class="topbar-right">
        <div class="locale-pill">
            <i class="fas fa-language" style="color: var(--accent);"></i>
            <a href="{{ route('locale', 'fr') }}" class="locale-link {{ app()->getLocale() === 'fr' ? 'is-active' : '' }}">FR</a>
            <a href="{{ route('locale', 'en') }}" class="locale-link {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">EN</a>
        </div>

        <div class="topbar-user">
            <div class="topbar-user-copy">
                <div class="topbar-user-name">{{ auth()->user()->name }}</div>
                <div class="topbar-user-role">{{ __('ui.' . auth()->user()->role) }}</div>
            </div>
            <div class="avatar-circle avatar-circle--brand">
                <x-clinic-logo />
            </div>
        </div>
    </div>
</header>
