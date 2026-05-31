#!/usr/bin/env php
<?php
declare(strict_types=1);

$root  = dirname(__DIR__);
$php   = PHP_BINARY;
$isWin = PHP_OS_FAMILY === 'Windows';

echo "起動中...\n";
echo "  App : http://localhost:8080\n";
echo "  CMS : http://localhost:5173\n";
echo "停止: Ctrl+C\n\n";

$desc = [STDIN, STDOUT, STDERR];
$pipes = [];

$app = proc_open(
    [$php, '-S', 'localhost:8080', '-t', $root . '/public'],
    $desc,
    $pipes,
    $root
);

$cmsCmd = $isWin ? ['cmd', '/c', 'npm run dev'] : ['npm', 'run', 'dev'];
$cms = proc_open(
    $cmsCmd,
    $desc,
    $pipes,
    $root . '/cms'
);

if (!is_resource($app) || !is_resource($cms)) {
    echo "プロセスの起動に失敗しました。\n";
    exit(1);
}

// Poll until either process exits, then terminate both
while (true) {
    $appRunning = proc_get_status($app)['running'];
    $cmsRunning = proc_get_status($cms)['running'];
    if (!$appRunning || !$cmsRunning) {
        proc_terminate($app);
        proc_terminate($cms);
        break;
    }
    usleep(300000);
}

proc_close($app);
proc_close($cms);
