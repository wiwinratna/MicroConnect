<?php
$umkms = App\Models\Umkm::all();
foreach($umkms as $umkm) {
    if (!App\Models\Coa::where('umkm_id', $umkm->id)->exists()) {
        $umkm->seedDefaultCoa();
        echo "Seeded COA for UMKM ID: " . $umkm->id . "\n";
    }
}
echo "Done!\n";
