<?php

$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/proyek_kadin/resources/views');
$iterator = new RecursiveIteratorIterator($dir);

$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // Replace Rp {{ number_format($var, 0, ',', '.') }} with {{ rupiah($var) }}
        $content = preg_replace(
            '/Rp\s*\{\{\s*number_format\(\s*(.+?)\s*,\s*0\s*,\s*\'\,\'\s*,\s*\'\.\'\s*\)\s*\}\}/',
            '{{ rupiah($1) }}',
            $content
        );

        // Replace (Rp {{ number_format(...) }}) => ({{ rupiah(...) }})
        $content = preg_replace(
            '/\(\s*Rp\s*\{\{\s*number_format\(\s*(.+?)\s*,\s*0\s*,\s*\'\,\'\s*,\s*\'\.\'\s*\)\s*\}\}\s*\)/',
            '({{ rupiah($1) }})',
            $content
        );
        
        // Also replace Rp. or Rp . variations
        $content = preg_replace(
            '/Rp\.?\s*\{\{\s*number_format\(\s*(.+?)\s*,\s*0\s*,\s*\'\,\'\s*,\s*\'\.\'\s*\)\s*\}\}/',
            '{{ rupiah($1) }}',
            $content
        );

        // Replace any remaining number_format(..., 2 or 3, ...) with format_angka(...)
        $content = preg_replace(
            '/number_format\(\s*(.+?)\s*,\s*[23]\s*,\s*\'\,\'\s*,\s*\'\.\'\s*\)/',
            'format_angka($1)',
            $content
        );
        
        // Replace any remaining number_format(..., 0, ...) that wasn't prefixed with Rp
        // It's safer to use format_angka for these just in case they are qty or something else. 
        // Wait, some might be money without Rp prefix (e.g., input values or table cells).
        // Let's just use format_angka() which works for integers without Rp.
        $content = preg_replace(
            '/number_format\(\s*(.+?)\s*,\s*0\s*,\s*\'\,\'\s*,\s*\'\.\'\s*\)/',
            'format_angka($1)',
            $content
        );
        
        if ($content !== $original) {
            file_put_contents($path, $content);
            $count++;
            echo "Updated: $path\n";
        }
    }
}

echo "Total files updated: $count\n";
