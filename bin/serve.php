#!/usr/bin/env php
<?php
declare(strict_types=1);

$root  = dirname(__DIR__);
$php   = PHP_BINARY;
$isWin = PHP_OS_FAMILY === 'Windows';
$sep   = DIRECTORY_SEPARATOR;

// Prerequisite checks
$autoload   = $root . $sep . 'vendor' . $sep . 'autoload.php';
$nodeModules = $root . $sep . 'cms' . $sep . 'node_modules';

if (!file_exists($autoload)) {
    echo "\033[31m[ERROR] vendor/ が見つかりません。先に composer setup を実行してください。\033[0m\n";
    exit(1);
}
if (!is_dir($nodeModules)) {
    echo "\033[31m[ERROR] cms/node_modules が見つかりません。先に composer setup を実行してください。\033[0m\n";
    exit(1);
}

$desc  = [STDIN, STDOUT, STDERR];
$pipes = [];

$app = proc_open(
    [$php, '-S', 'localhost:8080', '-t', $root . '/public'],
    $desc, $pipes, $root
);

$cmsDir = $root . $sep . 'cms';
$cmsCmd = $isWin ? 'npm run dev' : ['npm', 'run', 'dev'];
$cms    = proc_open($cmsCmd, $desc, $pipes, $cmsDir);

if (!is_resource($app)) {
    echo "\033[31m[ERROR] App サーバーの起動に失敗しました。\033[0m\n";
    exit(1);
}

echo "起動中...\n";
echo "  App : http://localhost:8080\n";

if (!is_resource($cms)) {
    echo "\033[33m  CMS : 起動失敗 (composer setup を再実行してください)\033[0m\n\n";
} else {
    echo "  CMS : http://localhost:5173\n";
}
echo "停止: Ctrl+C\n\n";

while (true) {
    $appRunning = proc_get_status($app)['running'];
    $cmsRunning = is_resource($cms) && proc_get_status($cms)['running'];

    if (!$appRunning) {
        echo "\nApp サーバーが停止しました。\n";
        if (is_resource($cms)) proc_terminate($cms);
        break;
    }
    if (is_resource($cms) && !$cmsRunning) {
        echo "\nCMS dev サーバーが停止しました。\n";
        echo "App サーバーは継続中 (Ctrl+C で停止)\n\n";
        while (proc_get_status($app)['running']) {
            usleep(300000);
        }
        break;
    }
    usleep(300000);
}

proc_close($app);
if (is_resource($cms)) proc_close($cms);
