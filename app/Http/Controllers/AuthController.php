<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Tampilkan form login
    public function showLoginForm()
    {
        return view('auth.login'); // resources/views/auth/login.blade.php
    }

    // Proses login
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Coba login (tanpa cek role dulu)
        if (!Auth::attempt([
            'email'    => $request->email,
            'password' => $request->password,
        ], $request->boolean('remember'))) {

            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        // Regenerasi session
        $request->session()->regenerate();

        $user = Auth::user();

        // Arahkan sesuai role
        if ($user->user_group === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        // Kalau bukan admin, anggap UMKM
        return redirect()->route('umkm.dashboard');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // (OPSIONAL) Tampilkan form register (kalau mau)
    public function showRegisterForm()
    {
        return view('auth.register'); 
    }

    public function register(Request $request)
    {
        // VALIDASI
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // BUAT USER BARU UMKM
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_group' => 'umkm', // 🟩 default pelaku usaha
        ]);

        // LOGIN OTOMATIS SETELAH REGISTER
        Auth::login($user);

        return redirect()->route('umkm.dashboard')->with('success', 'Akun berhasil dibuat!');
    }
}
