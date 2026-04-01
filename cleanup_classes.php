<?php

$dir = new RecursiveDirectoryIterator('c:/xampp/htdocs/proyek_kadin/resources/views');
$iterator = new RecursiveIteratorIterator($dir);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;

        // Cleanup: <td class="text-end" class="text-end fw-medium"> 
        // -> <td class="text-end fw-medium">
        $content = preg_replace_callback('/<td([^>]+)>/i', function($m) {
            $inner = $m[1];
            // Extract all classes from class="..." attributes
            preg_match_all('/class="([^"]+)"/i', $inner, $matches);
            
            if (count($matches[1]) > 1) {
                // Collect all classes
                $allClasses = [];
                foreach ($matches[1] as $cStr) {
                    $classes = explode(' ', $cStr);
                    foreach ($classes as $c) {
                        if (trim($c) !== '') {
                            $allClasses[] = trim($c);
                        }
                    }
                }
                $allClasses = array_unique($allClasses);
                
                // Remove all class attributes from inner
                $inner = preg_replace('/class="[^"]*"\s*/i', '', $inner);
                
                // Append merged class
                if (!empty($allClasses)) {
                    $inner .= ' class="' . implode(' ', $allClasses) . '"';
                }
            }
            return '<td' . rtrim($inner) . '>';
        }, $content);

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Cleaned up classes in: $path\n";
        }
    }
}
