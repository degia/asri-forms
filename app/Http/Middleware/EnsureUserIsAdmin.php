<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user()?->hasRole('admin')) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses panel ini.');
        }

        return $next($request);
    }
}
