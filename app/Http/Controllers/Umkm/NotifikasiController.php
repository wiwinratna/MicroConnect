<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;

/**
 * Kotak Notifikasi untuk UMKM: hanya salinan aplikasi miliknya sendiri.
 */
class NotifikasiController extends Controller
{
    public function index()
    {
        $umkm = auth()->user()->umkm;

        $notifikasi = $umkm
            ? Notifikasi::where('umkm_id', $umkm->id)->where('saluran', 'aplikasi')->latest('id')->paginate(15)
            : Notifikasi::whereRaw('1 = 0')->paginate(15);

        return view('umkm.notifikasi.index', compact('notifikasi'));
    }
}
