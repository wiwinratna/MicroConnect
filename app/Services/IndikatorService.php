<?php

namespace App\Services;

use App\Models\Indikator;
use App\Models\UmkmLevel;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * FR-15: Admin mengelola indikator, pilihan jawaban, dan batas skor level.
 * Aturan yang dijaga: batas level selalu mencakup semua total skor yang mungkin (BR-09, BR-12),
 * yang pernah dijawab hanya boleh dinonaktifkan (BR-10), dan riwayat penilaian tidak berubah
 * karena skor serta teks jawaban sudah disalin ke detail_penilaian.
 */
class IndikatorService
{
    /** Indikator yang pilihannya diisi otomatis dari pembukuan dikenali dari awalan nama. */
    private const NAMA_SISTEM = ['omzet', 'modal'];

    public static function namaSistem(string $nama): bool
    {
        $nama = strtolower(trim($nama));

        foreach (self::NAMA_SISTEM as $awalan) {
            if (str_starts_with($nama, $awalan)) {
                return true;
            }
        }

        return false;
    }

    public function pernahDijawab(Indikator $indikator): bool
    {
        return DB::table('detail_penilaian')->where('indikator_id', $indikator->id)->exists();
    }

    /** @return array{0:int,1:int} total skor terendah dan tertinggi dari indikator aktif */
    public function rentangSkor(): array
    {
        $perIndikator = Indikator::aktif()->with('pilihan')->get()
            ->map(fn (Indikator $i) => $i->pilihan->pluck('skor'));

        return [
            (int) $perIndikator->sum(fn (Collection $s) => $s->min() ?? 0),
            (int) $perIndikator->sum(fn (Collection $s) => $s->max() ?? 0),
        ];
    }

    /**
     * Simpan indikator beserta pilihannya. Indikator baru disimpan nonaktif agar batas
     * level bisa disesuaikan dulu.
     */
    public function simpan(array $data, User $admin, ?Indikator $indikator = null): Indikator
    {
        $baru = $indikator === null;

        if (!$baru && self::namaSistem($indikator->nama)) {
            $data['nama'] = $indikator->nama;
        } elseif (self::namaSistem($data['nama'])) {
            $this->tolak('nama', 'Nama yang diawali "omzet" atau "modal" dipakai sistem untuk pengisian otomatis. Gunakan nama lain.');
        }

        return DB::transaction(function () use ($data, $admin, $indikator, $baru) {
            $lama = $baru ? null : $this->potret($indikator);
            $indikator ??= new Indikator(['aktif' => false, 'sumber_data' => 'pilihan_jawaban']);

            $indikator->fill([
                'nama'       => $data['nama'],
                'definisi'   => $data['definisi'] ?? null,
                'pertanyaan' => $data['pertanyaan'],
                'urutan'     => $data['urutan'],
            ])->save();

            $this->simpanPilihan($indikator, array_values($data['pilihan']));

            if ($indikator->aktif) {
                $this->pastikanTercakup(UmkmLevel::orderBy('urutan')->get(), 'pilihan');
            }

            $this->catat($admin, $baru ? 'indikator.buat' : 'indikator.ubah', $indikator->id, $lama, $this->potret($indikator));

            return $indikator;
        });
    }

    public function ubahStatus(Indikator $indikator, User $admin): Indikator
    {
        return DB::transaction(function () use ($indikator, $admin) {
            if ($indikator->aktif && Indikator::aktif()->count() <= 1) {
                $this->tolak('status', 'Harus ada paling sedikit satu pertanyaan aktif.');
            }

            if (!$indikator->aktif && $indikator->pilihan()->count() < 2) {
                $this->tolak('status', 'Pertanyaan perlu paling sedikit dua pilihan jawaban.');
            }

            $indikator->update(['aktif' => !$indikator->aktif]);
            $this->pastikanTercakup(UmkmLevel::orderBy('urutan')->get(), 'status');
            $this->catat($admin, 'indikator.status', $indikator->id,
                ['aktif' => !$indikator->aktif], ['aktif' => $indikator->aktif]);

            return $indikator;
        });
    }

    public function hapus(Indikator $indikator, User $admin): void
    {
        DB::transaction(function () use ($indikator, $admin) {
            if ($this->pernahDijawab($indikator)) {
                $this->tolak('status', 'Pertanyaan ini sudah pernah dijawab, jadi hanya bisa dinonaktifkan.');
            }

            if (self::namaSistem($indikator->nama)) {
                $this->tolak('status', 'Pertanyaan omzet dan modal dipakai sistem, jadi hanya bisa dinonaktifkan.');
            }

            if ($indikator->aktif && Indikator::aktif()->count() <= 1) {
                $this->tolak('status', 'Harus ada paling sedikit satu pertanyaan aktif.');
            }

            $lama = $this->potret($indikator);
            $id = $indikator->id;
            $indikator->delete();
            $this->pastikanTercakup(UmkmLevel::orderBy('urutan')->get(), 'status');
            $this->catat($admin, 'indikator.hapus', $id, $lama, null);
        });
    }

    /** @param array<int,array{min:int,max:int}> $batas dikunci id level */
    public function simpanBatas(array $batas, User $admin): void
    {
        DB::transaction(function () use ($batas, $admin) {
            $levels = UmkmLevel::orderBy('urutan')->lockForUpdate()->get();
            $lama = $levels->mapWithKeys(fn ($l) => [$l->kode => [$l->skor_min, $l->skor_max]])->all();

            foreach ($levels as $level) {
                if (!isset($batas[$level->id])) {
                    $this->tolak('batas', "Batas skor {$level->nama_level} wajib diisi.");
                }
                $level->skor_min = (int) $batas[$level->id]['min'];
                $level->skor_max = (int) $batas[$level->id]['max'];
            }

            $this->pastikanTercakup($levels, 'batas');
            $levels->each->save();

            $this->catat($admin, 'level.batas', $levels->first()->id, $lama,
                $levels->mapWithKeys(fn ($l) => [$l->kode => [$l->skor_min, $l->skor_max]])->all());
        });
    }

    /** Batas harus berurutan tanpa celah atau tumpang tindih dan mencakup seluruh rentang skor. */
    private function pastikanTercakup(Collection $levels, string $kolom): void
    {
        $sebelumnya = null;

        foreach ($levels as $level) {
            if ($level->skor_min === null || $level->skor_max === null || $level->skor_min > $level->skor_max) {
                $this->tolak($kolom, "{$level->nama_level}: skor terendah tidak boleh melebihi skor tertinggi.");
            }

            if ($sebelumnya !== null && (int) $level->skor_min !== (int) $sebelumnya->skor_max + 1) {
                $this->tolak($kolom, "{$level->nama_level} harus mulai dari skor " . ($sebelumnya->skor_max + 1)
                    . " agar batas bersambung dengan {$sebelumnya->nama_level}.");
            }

            $sebelumnya = $level;
        }

        [$min, $max] = $this->rentangSkor();

        if ($levels->first()->skor_min > $min || $levels->last()->skor_max < $max) {
            $this->tolak($kolom, "Total skor bisa antara {$min} dan {$max}, tetapi batas level hanya mencakup "
                . "{$levels->first()->skor_min} sampai {$levels->last()->skor_max}. Sesuaikan batas skor level lebih dulu.");
        }
    }

    /** Pilihan lama diperbarui, yang baru ditambah, yang dihilangkan dihapus bila belum pernah dipilih. */
    private function simpanPilihan(Indikator $indikator, array $daftar): void
    {
        $ada = $indikator->pilihan()->get()->keyBy('id');
        $dipakai = [];

        foreach ($daftar as $i => $p) {
            $isi = [
                'teks'                => $p['teks'],
                'contoh'              => $p['contoh'] ?? null,
                'skor'                => $p['skor'],
                'di_luar_batas_mikro' => !empty($p['di_luar_batas_mikro']),
                'urutan'              => $i + 1,
            ];

            // Rentang angka hanya dikirim untuk indikator yang diisi otomatis dari pembukuan.
            if (self::namaSistem($indikator->nama)) {
                $isi['nilai_min'] = $p['nilai_min'] ?? null;
                $isi['nilai_max'] = $p['nilai_max'] ?? null;
            }

            $lama = !empty($p['id']) ? $ada->get((int) $p['id']) : null;

            if ($lama) {
                $lama->update($isi);
                $dipakai[] = $lama->id;
            } else {
                $dipakai[] = $indikator->pilihan()->create($isi)->id;
            }
        }

        foreach ($ada->except($dipakai) as $buang) {
            if (DB::table('detail_penilaian')->where('pilihan_id', $buang->id)->exists()) {
                $this->tolak('pilihan', "Pilihan \"{$buang->teks}\" sudah pernah dipilih, jadi tidak bisa dihapus.");
            }
            $buang->delete();
        }
    }

    private function potret(Indikator $indikator): array
    {
        return $indikator->only(['nama', 'pertanyaan', 'urutan', 'aktif']) + [
            'pilihan' => $indikator->pilihan()->get()
                ->map(fn ($p) => $p->only(['teks', 'skor', 'di_luar_batas_mikro']))->all(),
        ];
    }

    private function catat(User $admin, string $aksi, int $id, ?array $lama, ?array $baru): void
    {
        DB::table('log_audit')->insert([
            'pengguna_id' => $admin->id,
            'aksi'        => $aksi,
            'entitas'     => str_starts_with($aksi, 'level') ? 'umkm_level' : 'indikator',
            'entitas_id'  => $id,
            'data_lama'   => $lama === null ? null : json_encode($lama),
            'data_baru'   => $baru === null ? null : json_encode($baru),
            'waktu'       => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    private function tolak(string $kolom, string $pesan): never
    {
        throw ValidationException::withMessages([$kolom => $pesan]);
    }
}
