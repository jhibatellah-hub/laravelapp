<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = Session::get('locale');

        if (!$locale && Auth::check()) {
            $locale = Auth::user()->locale;
        }

        App::setLocale(in_array($locale, ['fr', 'en'], true) ? $locale : 'fr');

        return $next($request);
    }
}
