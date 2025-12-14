<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // =========================
    // FORM LOGIN
    // =========================
    public function showUmkmLoginForm()
    {
        return view('auth.login', [
            'formAction' => route('umkm.login.process'),
            'title'      => 'Login UMKM',
            'subtitle'   => 'Login untuk pelaku usaha',
        ]);
    }


    public function showAdminLoginForm()
    {
        return view('auth.admin-login'); // login Admin
    }

    // =========================
    // LOGIN UMKM
    // =========================
    public function loginUmkm(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        // cegah admin login lewat halaman UMKM
        if (auth()->user()->user_group !== 'pelakuusaha') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun ini bukan akun UMKM.',
            ])->onlyInput('email');
        }

        return redirect()->route('umkm.dashboard');
    }

    // =========================
    // LOGIN ADMIN
    // =========================
    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        // cegah UMKM login lewat halaman admin
        if (auth()->user()->user_group !== 'admin') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun ini bukan akun Admin.',
            ])->onlyInput('email');
        }

        return redirect()->route('admin.dashboard');
    }

    // =========================
    // REGISTER UMKM
    // =========================
    public function showRegisterForm()
    {
        return view('auth.register'); // register UMKM
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Buat user UMKM (dipaksa)
        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'user_group' => 'pelakuusaha', // ✅ jangan diganti dulu
        ]);

        // Buat data UMKM default
        $kodeUmkm = Umkm::getKodeUmkm();

        Umkm::create([
            'kode_umkm' => $kodeUmkm,
            'user_id'   => $user->id,
            'level_id'  => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('umkm.level.choose')
            ->with('success', 'Akun berhasil dibuat, silakan pilih level UMKM.');
    }

    // =========================
    // REGISTER ADMIN (opsional)
    // =========================
    public function showAdminRegisterForm()
    {
        return view('auth.admin-register');
    }

    public function registerAdmin(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6|confirmed',
            'admin_code' => 'required',
        ]);

        // pengaman: harus ada kode
        if ($request->admin_code !== env('ADMIN_REGISTER_CODE')) {
            return back()->withErrors(['admin_code' => 'Kode admin salah'])->withInput();
        }

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'user_group' => 'admin',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    // =========================
    // LOGOUT
    // =========================
    public function logout(Request $request)
    {
        $role = auth()->check() ? auth()->user()->user_group : null;

        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // arahkan sesuai role terakhir
        if ($role === 'admin') {
            return redirect()->route('admin.login');
        }

        return redirect()->route('umkm.login');
    }
}
