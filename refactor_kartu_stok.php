<?php
$path = 'c:/xampp/htdocs/proyek_kadin/resources/views/umkm/laporan/kartu_stok.blade.php';
$content = file_get_contents($path);

// 1. Clean up duplicate font weights `fw-semibold fw-medium` -> `fw-medium` or `fw-semibold` -> `fw-medium`
$content = str_replace('fw-semibold fw-medium', 'fw-medium', $content);
$content = str_replace('fw-bold bg-light fw-medium', 'fw-bold', $content);
$content = str_replace('text-primary bg-light fw-medium', 'text-primary', $content);
$content = str_replace('bg-light fw-medium', '', $content);
$content = str_replace('class="text-end text-primary fw-bold bg-light"', 'class="text-end text-dark fw-bold"', $content);
$content = str_replace('class="text-end text-primary bg-light"', 'class="text-end text-dark fw-medium"', $content);
$content = str_replace('text-end text-primary fw-bold', 'text-end text-dark fw-bold', $content);
$content = str_replace('text-end text-primary', 'text-end text-dark', $content);

// Ensure the border for columns
$content = preg_replace('/<td\s+class="text-center text-nowrap"([^>]*)>/', '<td class="text-center text-muted" style="border-right: 1px solid #e2e8f0; font-size: 13px;"$1>', $content);
$content = preg_replace('/<td([^>]*)>\s*<span\s+class="badge/', '<td style="border-right: 1px solid #e2e8f0;"$1>\n                            <span class="badge', $content);

// Also add right borders to the MASUK and KELUAR column group ends
$content = str_replace('<td  class="text-end text-success fw-medium">{{ rupiah($masukNilai) }}</td>', '<td  class="text-end text-success fw-medium" style="border-right: 1px solid #e2e8f0;">{{ rupiah($masukNilai) }}</td>', $content);
$content = preg_replace('/<td class="text-center text-muted">—<\/td>\s*<td class="text-center text-muted">—<\/td>\s*<td class="text-center text-muted">—<\/td>/', '<td class="text-center text-muted" style="border-right: 1px solid #e2e8f0;" colspan="3">—</td>', $content);

file_put_contents($path, $content);
echo "Kartu Stok Cleaned Up!";
