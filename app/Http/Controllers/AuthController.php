<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Umkm;
use App\Models\PengajuanPendaftaran;
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
            // Calon UMKM belum punya akun: beri tahu bahwa pendaftarannya masih menunggu.
            $menunggu = PengajuanPendaftaran::where('status', 'menunggu')
                ->where('email', strtolower(trim((string) $request->email)))
                ->exists();

            return back()->withErrors([
                'email' => $menunggu
                    ? 'Pendaftaran Anda masih menunggu verifikasi Admin.'
                    : 'Email atau password salah.',
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

        // FR-07 / BR-07: hanya akun UMKM berstatus aktif yang boleh login.
        $umkm = auth()->user()->umkm;
        if ($umkm && $umkm->status !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda sedang nonaktif. Hubungi Admin KADIN.',
            ])->onlyInput('email');
        }

        // Jika user harus ganti password sementara
        if (auth()->user()->must_change_password) {
            return redirect()->route('password.change')
                ->with('warning', 'Anda harus mengganti password sementara terlebih dahulu.');
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
    // REGISTER UMKM -> lihat PendaftaranController (v1.1: pengajuan, bukan akun langsung)
    // =========================

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

    // =========================
    // GANTI PASSWORD (WAJIB)
    // =========================

    /**
     * Form ganti password sementara.
     * Ditampilkan saat user dengan must_change_password=true login.
     */
    public function changePassword()
    {
        return view('auth.change-password');
    }

    /**
     * Proses ganti password sementara.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
        ]);

        $user = auth()->user();
        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('umkm.dashboard')
            ->with('success', 'Password berhasil diubah. Selamat menggunakan sistem!');
    }
}
