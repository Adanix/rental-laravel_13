<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        $userRole = $user->role->value ?? (string) $user->role;

        if (! in_array($userRole, $roles, true)) {
            abort(403, 'Akses tidak diizinkan. Peran akun Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
