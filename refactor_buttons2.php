<?php
$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/proyek_kadin/resources/views/umkm');
$iterator = new RecursiveIteratorIterator($dir);

$filesChanged = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // WhatsApp Button
        $content = preg_replace_callback(
            '/<a[^>]*title="Hubungi\s+WhatsApp"[^>]*>.*?Hubungi\s+WhatsApp\s*<\/a>/is',
            function($m) {
                // Ensure we capture the href correctly
                preg_match('/href="([^"]+)"/i', $m[0], $hrefMatch);
                $href = $hrefMatch[1] ?? '#';
                return '<a href="'.$href.'" target="_blank" class="btn btn-sm btn-action btn-action-whatsapp" title="Hubungi WhatsApp"><i data-feather="message-circle" class="me-1"></i> WhatsApp</a>';
            },
            $content
        );

        // Delete button with `type="submit"` which was missed in previous pass
        $content = preg_replace_callback(
            '/<button[^>]*class="[^"]*btn-danger[^"]*"[^>]*>.*?Hapus.*?<\/button>/is',
            function($m) {
                if(strpos($m[0], 'btn-action-delete') !== false) return $m[0]; // Already refactored
                return '<button type="submit" class="btn btn-sm btn-action btn-action-delete" title="Hapus"><i data-feather="trash-2"></i> Hapus</button>';
            },
            $content
        );

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Refactored missing buttons in: $path\n";
            $filesChanged++;
        }
    }
}
echo "\nTotal files refactored: $filesChanged\n";
