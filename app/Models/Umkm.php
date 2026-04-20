<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Umkm extends Model
{
    use HasFactory;
    protected $table = 'umkm';

    protected $fillable = [
        'kode_umkm',
        'user_id',
        'level_id',
        'nama_usaha',
        'nib',
        'alamat',
        'no_telepon',
        // Fields baru
        'jenis_usaha',
        'no_whatsapp',
        'recording_method',
        'inventory_method',
        'status',
        'logo_path',
        'warna_tema',
    ];

    protected $attributes = [
        'recording_method' => 'periodik',
        'inventory_method' => 'Average',
        'status'           => 'aktif',
    ];

    // ===================== HELPERS =====================

    public static function getKodeUmkm(): string
    {
        $last = self::orderByDesc('created_at')->first();
        $lastNumber = $last ? (int) substr($last->kode_umkm, -5) : 0;
        return 'UM' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Buat COA default untuk UMKM baru.
     * Idempotent: hanya seed jika belum ada COA sama sekali.
     */
    public function seedDefaultCoa(): void
    {
        // Jangan seed kalau sudah ada COA
        if (Coa::where('umkm_id', $this->id)->exists()) {
            return;
        }

        $defaults = [
            // [header_akun, kode_akun, nama_akun, posisi_dr_cr]
            // ── ASET ──
            ['1', '111', 'Kas',                      'Debit'],
            ['1', '112', 'Bank',                      'Debit'],
            ['1', '113', 'Piutang Usaha',             'Debit'],
            ['1', '114', 'Persediaan Bahan Baku',     'Debit'],
            ['1', '115', 'Persediaan Produk Jadi',    'Debit'],
            ['1', '116', 'Perlengkapan',              'Debit'],
            // ── KEWAJIBAN ──
            ['2', '211', 'Utang Usaha',               'Kredit'],
            ['2', '212', 'Utang Gaji',                'Kredit'],
            // ── MODAL ──
            ['3', '311', 'Modal Pemilik',             'Kredit'],
            ['3', '312', 'Prive',                     'Debit'],
            // ── PENDAPATAN ──
            ['4', '400', 'Pendapatan Penjualan',      'Kredit'],
            ['4', '401', 'Pendapatan Lain-lain',      'Kredit'],
            // ── BEBAN ──
            ['5', '501', 'Harga Pokok Penjualan',     'Debit'],
            ['5', '502', 'Beban Bahan Baku',          'Debit'],
            ['5', '503', 'Beban Gaji',                'Debit'],
            ['5', '504', 'Beban Sewa',                'Debit'],
            ['5', '505', 'Beban Listrik & Air',       'Debit'],
            ['5', '506', 'Beban Perlengkapan',        'Debit'],
            ['5', '507', 'Beban Overhead',            'Debit'],
            ['5', '508', 'Beban Lain-lain',           'Debit'],
        ];

        $rows = array_map(fn($d) => [
            'umkm_id'      => $this->id,
            'header_akun'  => $d[0],
            'kode_akun'    => $d[1],
            'nama_akun'    => $d[2],
            'posisi_dr_cr' => $d[3],
            'created_at'   => now(),
            'updated_at'   => now(),
        ], $defaults);

        Coa::insert($rows);
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    // ===================== RELASI =====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function level()
    {
        return $this->belongsTo(UmkmLevel::class, 'level_id');
    }

    public function bahanBaku()
    {
        return $this->hasMany(BahanBaku::class, 'umkm_id');
    }

    public function produk()
    {
        return $this->hasMany(Produk::class, 'umkm_id');
    }

    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'umkm_id');
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'umkm_id');
    }

    public function stokMutasi()
    {
        return $this->hasMany(StokMutasi::class, 'umkm_id');
    }

    public function pelanggan()
    {
        return $this->hasMany(Pelanggan::class, 'umkm_id');
    }

    public function piutang()
    {
        return $this->hasMany(Piutang::class, 'umkm_id');
    }

    public function iuranBulanan()
    {
        return $this->hasMany(IuranBulanan::class, 'umkm_id');
    }
}
