<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('login') }} — MedCabinet</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #0C447C 0%, #185FA5 50%, #378ADD 100%);
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #fff; border-radius: 16px;
            padding: 40px; width: 100%; max-width: 400px;
            box-shadow: 0 20px 60px rgba(4,44,83,0.3);
        }
        .brand { text-align: center; margin-bottom: 32px; }
        .brand-icon {
            width: 56px; height: 56px; border-radius: 14px;
            background: #0C447C;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
        }
        .brand-icon i { color: white; font-size: 24px; }
        .brand-name { font-size: 22px; font-weight: 700; color: #0C447C; }
        .brand-sub  { font-size: 13px; color: #6b7280; margin-top: 4px; }
        h2 { font-size: 18px; font-weight: 600; color: #1a1a2e; margin-bottom: 6px; }
        .subtitle { font-size: 13px; color: #6b7280; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 12px; font-weight: 500; color: #6b7280; margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #e5e7eb; border-radius: 9px;
            font-size: 14px; color: #1a1a2e; font-family: inherit;
            transition: border-color 0.15s;
        }
        .form-control:focus { outline: none; border-color: #185FA5; box-shadow: 0 0 0 3px rgba(24,95,165,0.1); }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 13px; }
        .input-wrap .form-control { padding-left: 36px; }
        .btn-login {
            width: 100%; padding: 11px;
            background: #185FA5; color: white;
            border: none; border-radius: 9px;
            font-size: 14px; font-weight: 600;
            cursor: pointer; font-family: inherit;
            transition: background 0.15s;
            margin-top: 8px;
        }
        .btn-login:hover { background: #0C447C; }
        .alert-error {
            background: #FCEBEB; color: #A32D2D;
            border: 1px solid #F7C1C1; border-radius: 8px;
            padding: 10px 14px; font-size: 13px;
            margin-bottom: 16px;
        }
        .link { color: #185FA5; font-size: 13px; text-decoration: none; }
        .link:hover { text-decoration: underline; }
        .divider { text-align: center; color: #9ca3af; font-size: 12px; margin: 16px 0; }
        .demo-info {
            background: #E6F1FB; border-radius: 8px; padding: 12px 14px;
            font-size: 12px; color: #185FA5; margin-top: 16px;
        }
        .demo-info strong { display: block; margin-bottom: 6px; }
        .demo-row { display: flex; justify-content: space-between; color: #0C447C; margin-top: 3px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand">
        <div class="brand-icon"><i class="fas fa-notes-medical"></i></div>
        <div class="brand-name">MedCabinet</div>
        <div class="brand-sub">{{ __('app.subtitle') }}</div>
    </div>

    <h2>{{ __('auth.welcome_back') }}</h2>
    <p class="subtitle">{{ __('auth.login_subtitle') }}</p>

    @if($errors->any())
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i>
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('auth.email') }}</label>
            <div class="input-wrap">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email') }}" required autofocus
                       placeholder="votre@email.com">
            </div>
        </div>
        <div class="form-group">
            <label class="form-label" style="display:flex;justify-content:space-between">
                {{ __('auth.password') }}
                <a href="#" class="link">{{ __('auth.forgot') }}</a>
            </label>
            <div class="input-wrap">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
        </div>
        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> {{ __('auth.login') }}
        </button>
    </form>

    <div class="divider">— ou —</div>
    <div style="text-align:center">
        <span style="font-size:13px;color:#6b7280">{{ __('auth.no_account') }}</span>
        <a href="{{ route('register') }}" class="link" style="margin-left:6px">{{ __('auth.register') }}</a>
    </div>

    <div class="demo-info">
        <strong><i class="fas fa-info-circle"></i> {{ __('auth.demo_accounts') }}</strong>
        <div class="demo-row"><span>Admin</span><span>admin@medcabinet.ma / password</span></div>
        <div class="demo-row"><span>Médecin</span><span>medecin@medcabinet.ma / password</span></div>
        <div class="demo-row"><span>Patient</span><span>patient@medcabinet.ma / password</span></div>
    </div>
</div>
</body>
</html>