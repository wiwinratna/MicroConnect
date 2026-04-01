<?php

$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/proyek_kadin/resources/views');
$iterator = new RecursiveIteratorIterator($dir);

$count = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // 1. Upgrade Table Classes
        // Look for <table class="..."> and inject modern classes if not present
        // Some might be <table class="table ...">
        $content = preg_replace_callback('/<table\s+class="([^"]+)"/i', function($matches) {
            $classes = explode(' ', $matches[1]);
            // Ensure necessary classes exist
            $required = ['table', 'table-hover', 'table-borderless', 'align-middle'];
            foreach ($required as $req) {
                if (!in_array($req, $classes)) {
                    $classes[] = $req;
                }
            }
            // Remove table-striped if present to make it cleaner, usually table-hover is enough for modern UI
            $classes = array_diff($classes, ['table-striped', 'table-bordered']);
            return '<table class="' . implode(' ', array_unique($classes)) . '"';
        }, $content);

        // 2. Action Buttons
        // Edit Button (outline-primary or primary) -> btn-action-edit
        $content = preg_replace(
            '/<a\s+href="([^"]+)"\s+class="btn\s+btn-sm\s+btn-(?:outline-)?primary[^"]*">\s*(?:<i[^>]*><\/i>\s*)?Edit\s*<\/a>/i',
            '<a href="$1" class="btn btn-sm btn-action btn-action-edit" title="Edit"><i data-feather="edit-2"></i> Edit</a>',
            $content
        );

        // Delete Button (outline-danger or danger) -> btn-action-delete
        $content = preg_replace(
            '/<button\s+class="btn\s+btn-sm\s+btn-(?:outline-)?danger[^"]*">\s*(?:<i[^>]*><\/i>\s*)?Hapus\s*<\/button>/i',
            '<button class="btn btn-sm btn-action btn-action-delete" title="Hapus"><i data-feather="trash-2"></i> Hapus</button>',
            $content
        );

        // View/Detail Button (outline-info or outline-secondary or outline-primary) depending on text -> btn-action-view
        // e.g. Detail / Lihat
        $content = preg_replace(
            '/<a\s+href="([^"]+)"\s+class="btn\s+btn-sm\s+btn-(?:outline-)?(?:info|secondary|primary)[^"]*">\s*(?:<i[^>]*><\/i>\s*)?(?:Detail|Lihat)\s*<\/a>/i',
            '<a href="$1" class="btn btn-sm btn-action btn-action-view" title="Detail"><i data-feather="eye"></i> Detail</a>',
            $content
        );

        // 3. Align Numbers
        // Find <td>{{ rupiah(...) }}</td> and make it <td class="text-end fw-medium">{{ rupiah(...) }}</td>
        $content = preg_replace(
            '/<td([^>]*)>\s*\{\{\s*rupiah\(/i',
            '<td$1 class="text-end fw-medium">{{ rupiah(',
            $content
        );

        $content = preg_replace(
            '/<td([^>]*)>\s*\{\{\s*format_angka\(/i',
            '<td$1 class="text-end fw-medium">{{ format_angka(',
            $content
        );

        // Clean up duplicate classes if we accidentally added text-end to something that already had it
        $content = preg_replace('/class="([^"]*)text-end(\s+[^"]*)?class="([^"]*)"/i', 'class="$1text-end$2 $3"', $content);
        // Better deduplication of text-end fw-medium
        $content = preg_replace_callback('/<td\s+class="([^"]+)"/i', function($matches) {
            $classes = explode(' ', $matches[1]);
            $classes = array_unique($classes);
            return '<td class="' . implode(' ', $classes) . '"';
        }, $content);

        // 4. Clean up thead
        $content = preg_replace('/<thead\s+class="(?!table-light)[^"]*"/i', '<thead class="table-light"', $content);
        // If it doesn't have a class:
        $content = preg_replace('/<thead>/i', '<thead class="table-light">', $content);

        if ($content !== $original) {
            file_put_contents($path, $content);
            $count++;
            echo "Upgrading Table UI: $path\n";
        }
    }
}

echo "Total Blade Table files updated: $count\n";
