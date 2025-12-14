<?php

namespace App\Http\Controllers;

use App\Models\Umkm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UmkmProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        $umkm = Umkm::firstOrCreate(
            ['user_id' => $user->id],
            [
                'level_id'   => null,
                'nama_usaha' => null,
                'nib'        => null,
                'alamat'     => null,
                'no_telepon' => null,
            ]
        );
        $umkm->load('level'); // <-- tambahin ini

        return view('umkm.profile', compact('user', 'umkm'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,' . $user->id,
            'password'  => 'nullable|string|min:6|confirmed',

            'nama_usaha' => 'nullable|string|max:255',
            'nib'        => 'nullable|string|max:100',
            'alamat'     => 'nullable|string|max:500',
            'no_telepon' => 'nullable|string|max:50',
        ]);

        // Update akun user
        $user->name  = $request->name;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // Update UMKM
        $umkm = Umkm::firstOrCreate(['user_id' => $user->id]);
        $umkm->nama_usaha = $request->nama_usaha;
        $umkm->nib        = $request->nib;
        $umkm->alamat     = $request->alamat;
        $umkm->no_telepon = $request->no_telepon;
        $umkm->save();

        return redirect()
            ->route('umkm.profile')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
