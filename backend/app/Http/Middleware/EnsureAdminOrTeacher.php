<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrTeacher
{
    /**
     * Handle an incoming request.
     * Only users with role 'admin' or 'teacher' (or is_admin=true) are allowed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login terlebih dahulu untuk mengakses panel admin.',
                ], 401);
            }

            return redirect()->route('admin.login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }

        $user = Auth::user();

        if (!$user->hasAdminAccess()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Hanya Administrator dan Guru/Pengawas yang diizinkan mengakses panel ini.',
                ], 403);
            }

            return redirect()->route('admin.login')->with('error', 'Akses ditolak! Akun Anda adalah akun Siswa. Hanya Administrator dan Guru/Pengawas yang diizinkan mengakses panel ini.');
        }

        return $next($request);
    }
}
