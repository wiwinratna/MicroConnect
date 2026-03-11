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
        $umkm->load('level');

        return view('umkm.profile', compact('user', 'umkm'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:users,email,' . $user->id,
            'password'          => 'nullable|string|min:6|confirmed',
            'nama_usaha'        => 'nullable|string|max:255',
            'nib'               => 'nullable|string|max:100',
            'alamat'            => 'nullable|string|max:500',
            'no_telepon'        => 'nullable|string|max:50',
            'jenis_usaha'       => 'nullable|string|max:50',
            'no_whatsapp'       => 'nullable|string|max:20',
            'recording_method'  => 'nullable|in:periodik,perpetual',
            'inventory_method'  => 'nullable|in:FIFO,LIFO,Average',
        ]);

        // Update akun user
        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // Update data UMKM (termasuk konfigurasi inventori)
        $umkm = Umkm::firstOrCreate(['user_id' => $user->id]);
        $umkm->update([
            'nama_usaha'       => $request->nama_usaha,
            'nib'              => $request->nib,
            'alamat'           => $request->alamat,
            'no_telepon'       => $request->no_telepon,
            'jenis_usaha'      => $request->jenis_usaha,
            'no_whatsapp'      => $request->no_whatsapp,
            'recording_method' => $request->recording_method ?? $umkm->recording_method,
            'inventory_method' => $request->inventory_method ?? $umkm->inventory_method,
        ]);

        return redirect()
            ->route('umkm.profile')
            ->with('success', 'Profil berhasil diperbarui.');
    }
}
