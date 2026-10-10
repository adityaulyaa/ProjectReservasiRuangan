<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCorrectDashboard
{
    /**
     * Handle an incoming request.
     * Redirect admin/staff ke dashboard mereka jika mengakses /dashboard
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user) {
            $userRole = is_object($user->role) ? $user->role->value : $user->role;

            if ($userRole === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($userRole === 'staff') {
                return redirect()->route('staff.dashboard');
            }
        }

        return $next($request);
    }
}
