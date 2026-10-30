<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminAuthController extends Controller
{
    /**
     * Show Admin Login Page
     */
    public function showLoginForm()
    {
        // If already logged in with admin/teacher access, go directly to dashboard
        if (Auth::check() && Auth::user()->hasAdminAccess()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Process Admin / Teacher Login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password'));
        }

        $username = strtolower(trim($request->input('username')));
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $user = User::where('username', $username)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Username atau password yang Anda masukkan salah!')
                ->withInput($request->except('password'));
        }

        // Verify role access (Only 'admin' and 'teacher' allowed)
        if (!$user->hasAdminAccess()) {
            return redirect()->back()
                ->with('error', 'Akses ditolak! Akun "' . $user->name . '" adalah akun Siswa. Hanya Administrator dan Guru/Pengawas yang diizinkan masuk ke panel web.')
                ->withInput($request->except('password'));
        }

        // Log the user in via web session guard
        Auth::login($user, $remember);
        $request->session()->regenerate();

        $roleText = $user->role === 'admin' ? 'Administrator' : 'Guru / Pengawas';

        return redirect()->route('admin.dashboard')
            ->with('success', 'Selamat datang kembali, ' . $user->name . ' (' . $roleText . ')!');
    }

    /**
     * Logout from Admin Panel
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Anda telah berhasil keluar dari panel admin.');
    }
}
