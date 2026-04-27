<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.login') }} - JamylCabinet</title>
    @vite(['resources/css/app.css'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="auth-screen">
        <div class="auth-shell">
            <div class="auth-locale">
                <div class="locale-pill">
                    <i class="fas fa-language" style="color: var(--accent);"></i>
                    <a href="{{ route('locale', 'fr') }}" class="locale-link {{ app()->getLocale() === 'fr' ? 'is-active' : '' }}">FR</a>
                    <a href="{{ route('locale', 'en') }}" class="locale-link {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">EN</a>
                </div>
            </div>

            @php
                $__clinicLogo = file_exists(public_path('images/clinic-profile-logo.png'))
                    ? asset('images/clinic-profile-logo.png')
                    : asset('images/clinic-profile-logo.svg');

                $__showcaseImage = null;
                foreach (['login-showcase.webp', 'login-showcase.jpg', 'login-showcase.jpeg', 'login-showcase.png'] as $__showcaseFile) {
                    if (file_exists(public_path('images/'.$__showcaseFile))) {
                        $__showcaseImage = asset('images/'.$__showcaseFile);
                        break;
                    }
                }
                if ($__showcaseImage === null) {
                    $__showcaseImage = asset('images/login-showcase.svg');
                }
            @endphp

            <div class="auth-card split">
                <div class="auth-showcase">
                    <div class="showcase-art showcase-art--clinic">
                        <div class="showcase-art-pattern" aria-hidden="true"></div>
                        <div class="showcase-logo-frame showcase-hero-frame">
                            <img class="showcase-hero" src="{{ $__showcaseImage }}" alt="">
                        </div>
                    </div>

                    <div class="showcase-copy">
                        <div class="showcase-title">{{ __('auth.showcase_title') }}</div>
                        <div class="showcase-text">{{ __('auth.showcase_text') }}</div>
                    </div>
                </div>

                <div class="auth-panel">
                    <div class="auth-brand">
                        <div class="auth-brand-icon auth-brand-icon--logo">
                            <img src="{{ $__clinicLogo }}" alt="">
                        </div>
                        <div>
                            <div class="auth-brand-title">Petal Health</div>
                            <div class="auth-brand-subtitle">{{ __('ui.medical_center') }}</div>
                        </div>
                    </div>

                    <div class="auth-heading">{{ __('auth.welcome_back') }}</div>
                    <div class="auth-copy">{{ __('auth.login_subtitle') }}</div>

                    @if($errors->any())
                        <div class="error-card" style="margin-top: 18px;">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="auth-form">
                        @csrf

                        <div class="field-wrap">
                            <div class="field-meta">{{ __('auth.email') }}</div>
                            <div class="input-shell">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('auth.email_placeholder') }}" required autofocus>
                            </div>
                        </div>

                        <div class="field-wrap">
                            <div class="field-meta">{{ __('auth.password') }}</div>
                            <div class="input-shell">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="{{ __('auth.password_placeholder') }}" required>
                            </div>
                        </div>

                        <div class="auth-inline">
                            <label class="checkbox-row">
                                <input type="checkbox" name="remember" value="1">
                                <span>{{ __('auth.remember') }}</span>
                            </label>
                            <a href="#" class="text-link">{{ __('auth.forgot') }}</a>
                        </div>

                        <button type="submit" class="btn btn-primary auth-submit">{{ __('auth.login_button') }}</button>
                    </form>

                    <div class="auth-support">{{ __('ui.support') }} <a href="#" class="text-link">{{ __('ui.contact_admin') }}</a></div>

                    <div class="auth-divider auth-footer">
                        {{ __('auth.no_account') }}
                        <a href="{{ route('register') }}" class="text-link">{{ __('auth.register') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
