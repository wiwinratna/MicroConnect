<?php
$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/proyek_kadin/resources/views/umkm');
$iterator = new RecursiveIteratorIterator($dir);

$filesChanged = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // 1. WhatsApp Button
        $content = preg_replace(
            '/<a\s+href="([^"]+)"\s+target="_blank"\s+class="btn\s+btn-sm\s+btn-success"\s+title="Hubungi\s+WhatsApp">\s*(?:<i[^>]*><\/i>\s*)?Hubungi\s+WhatsApp\s*<\/a>/i',
            '<a href="$1" target="_blank" class="btn btn-sm btn-action btn-action-whatsapp" title="Hubungi WhatsApp"><i data-feather="message-circle"></i> WhatsApp</a>',
            $content
        );

        // 2. Excel Button (Link)
        $content = preg_replace(
            '/<a\s+href="([^"]+)"\s+class="btn\s+btn-sm\s+btn-success">\s*(?:<i[^>]*><\/i>\s*)?Excel\s*<\/a>/i',
            '<a href="$1" class="btn btn-sm btn-action btn-action-excel" title="Unduh Excel"><i data-feather="file-text"></i> Excel</a>',
            $content
        );

        // 3. PDF Button (Link)
        $content = preg_replace(
            '/<a\s+href="([^"]+)"\s+class="btn\s+btn-sm\s+btn-danger">\s*(?:<i[^>]*><\/i>\s*)?(?:Export\s+)?PDF\s*<\/a>/i',
            '<a href="$1" class="btn btn-sm btn-action btn-action-pdf" title="Unduh PDF"><i data-feather="file"></i> PDF</a>',
            $content
        );

        // 4. Delete button with `type="submit"` which was missed in previous pass
        // e.g. <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
        $content = preg_replace(
            '/<button\s+type="submit"\s+class="btn\s+btn-sm\s+btn-(?:outline-)?danger[^"]*">\s*(?:<i[^>]*><\/i>\s*)?Hapus\s*<\/button>/i',
            '<button type="submit" class="btn btn-sm btn-action btn-action-delete" title="Hapus"><i data-feather="trash-2"></i> Hapus</button>',
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
