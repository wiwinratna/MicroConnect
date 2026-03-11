<?php

namespace App\Console\Commands;

use App\Services\IuranService;
use Illuminate\Console\Command;

class GenerateIuranCommand extends Command
{
    protected $signature   = 'iuran:generate {periode? : Format Y-m, default bulan ini}';
    protected $description = 'Generate tagihan iuran bulanan untuk semua UMKM aktif';

    public function __construct(private IuranService $iuranService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $periode = $this->argument('periode') ?? now()->format('Y-m');

        $this->info("Generate iuran untuk periode: {$periode}");

        $count = $this->iuranService->generateMonthly($periode);

        $this->info("Selesai: {$count} tagihan iuran baru dibuat untuk periode {$periode}.");

        return Command::SUCCESS;
    }
}
