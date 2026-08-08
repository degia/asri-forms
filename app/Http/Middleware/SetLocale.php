<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['id', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        if (Auth::check()) {
            $locale = Auth::user()->locale;
        } else {
            $locale = $request->session()->get('locale');
        }

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale', 'id');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
