<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('auth.register') }} - JamylCabinet</title>
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

            <div class="auth-card single">
                <div class="auth-panel compact">
                    <div class="auth-brand" style="justify-content: center;">
                        <div class="auth-brand-icon">
                            <i class="fas fa-briefcase-medical"></i>
                        </div>
                    </div>

                    <div class="auth-heading" style="text-align: center;">{{ __('auth.register_title') }}</div>
                    <div class="auth-copy" style="text-align: center;">{{ __('auth.register_subtitle') }}</div>

                    @if($errors->any())
                        <div class="error-card" style="margin-top: 18px;">
                            <ul style="display:grid; gap:6px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="auth-form">
                        @csrf

                        <div class="field-wrap">
                            <div class="field-meta">{{ __('auth.full_name') }}</div>
                            <div class="input-shell">
                                <i class="fas fa-user"></i>
                                <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('auth.name_placeholder') }}" required autofocus>
                            </div>
                        </div>

                        <div class="field-wrap">
                            <div class="field-meta">{{ __('auth.email') }}</div>
                            <div class="input-shell">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('auth.email_placeholder') }}" required>
                            </div>
                        </div>

                        <div class="field-wrap">
                            <div class="field-meta">{{ __('auth.phone') }}</div>
                            <div class="input-shell">
                                <i class="fas fa-phone"></i>
                                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="{{ __('auth.phone_placeholder') }}">
                            </div>
                        </div>

                        <div class="field-wrap">
                            <div class="field-meta">{{ __('auth.password') }}</div>
                            <div class="input-shell">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="{{ __('auth.password_placeholder') }}" required>
                            </div>
                        </div>

                        <div class="field-wrap">
                            <div class="field-meta">{{ __('auth.confirm_password') }}</div>
                            <div class="input-shell">
                                <i class="fas fa-shield-heart"></i>
                                <input type="password" name="password_confirmation" placeholder="{{ __('auth.password_placeholder') }}" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary auth-submit">{{ __('auth.create_account_button') }}</button>
                    </form>

                    <div class="auth-divider auth-footer">
                        {{ __('auth.already_registered') }}
                        <a href="{{ route('login') }}" class="text-link">{{ __('auth.login') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
