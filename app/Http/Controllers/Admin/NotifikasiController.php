<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notifikasi;
use App\Services\Notifikasi\TemplatPesan;
use Illuminate\Http\Request;

/**
 * Kotak Notifikasi untuk Admin: semua notifikasi (WhatsApp simulasi dan salinan aplikasi),
 * termasuk yang ditujukan ke calon UMKM yang belum punya akun. Dipakai untuk demo driver simulasi.
 */
class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $jenisValid = array_keys(TemplatPesan::TEMPLAT);

        $query = Notifikasi::with('umkm')->latest('id');

        if (in_array($request->jenis, $jenisValid, true)) {
            $query->where('jenis', $request->jenis);
        }
        if (in_array($request->saluran, ['whatsapp', 'aplikasi'], true)) {
            $query->where('saluran', $request->saluran);
        }
        if (in_array($request->status, ['antri', 'terkirim', 'gagal'], true)) {
            $query->where('status', $request->status);
        }

        return view('admin.notifikasi.index', [
            'notifikasi' => $query->paginate(20)->withQueryString(),
            'jenisList'  => $jenisValid,
        ]);
    }
}
