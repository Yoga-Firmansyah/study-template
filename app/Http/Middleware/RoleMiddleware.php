<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // 2. Ambil role user (sudah di-cast ke Enum di Model User)
        $userRole = auth()->user()->role;

        // 3. Cek apakah role user sesuai dengan yang diminta di route
        // Kita membandingkan value string dari Enum
        if ($userRole->value !== $role) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
