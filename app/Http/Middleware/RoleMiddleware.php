<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Admin memiliki akses ke segalanya (Opsional, tapi biasanya membantu)
        // Namun di SRS-05 diminta strict per peran, jadi kita cek keberadaan di array $roles
        if (in_array($user->role->value, $roles)) {
            return $next($request);
        }

        abort(403, 'Akses ditolak: Anda tidak memiliki peran yang diperlukan.');
    }
}
