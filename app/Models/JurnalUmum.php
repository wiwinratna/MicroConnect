<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalUmum extends Model
{

    protected $table = 'jurnal_umum';

    protected $fillable = [
    'umkm_id','tanggal','kode_akun','nama_akun','keterangan','debit','kredit',
    'ref_tipe','ref_id' // kalau kolomnya ada
    ];

}
