<?php
declare(strict_types=1);

$dir = dirname(__DIR__) . '/var/tmp';
if (!is_dir($dir)) {
    echo "Nothing to clear.\n";
    exit(0);
}

$count = 0;
$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
);
foreach ($iter as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.html.php')) {
        unlink($file->getPathname());
        $count++;
    }
}
echo "Cleared {$count} Qiq template cache file(s).\n";
