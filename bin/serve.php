#!/usr/bin/env php
<?php
declare(strict_types=1);

$root  = dirname(__DIR__);
$php   = PHP_BINARY;
$isWin = PHP_OS_FAMILY === 'Windows';

$desc  = [STDIN, STDOUT, STDERR];
$pipes = [];

// BearSunday app server
$app = proc_open(
    [$php, '-S', 'localhost:8080', '-t', $root . '/public'],
    $desc,
    $pipes,
    $root
);

// CMS dev server
// Windows: pass as string so cmd.exe /c handles tokenisation correctly
// Unix:    pass as array to avoid shell quoting issues
$cmsDir = $root . ($isWin ? '\cms' : '/cms');
$cmsCmd = $isWin ? 'npm run dev' : ['npm', 'run', 'dev'];
$cms    = proc_open($cmsCmd, $desc, $pipes, $cmsDir);

if (!is_resource($app)) {
    echo "[ERROR] App サーバーの起動に失敗しました。\n";
    exit(1);
}

echo "起動中...\n";
echo "  App : http://localhost:8080\n";

if (!is_resource($cms)) {
    echo "  CMS : 起動失敗 (npm がインストールされているか確認してください)\n";
    echo "停止: Ctrl+C\n\n";
    proc_close($app);
    exit(1);
}

echo "  CMS : http://localhost:5173\n";
echo "停止: Ctrl+C\n\n";

// Keep running while both processes are alive
while (true) {
    $appStatus = proc_get_status($app);
    $cmsStatus = proc_get_status($cms);

    if (!$appStatus['running']) {
        echo "\nApp サーバーが停止しました。\n";
        proc_terminate($cms);
        break;
    }
    if (!$cmsStatus['running']) {
        echo "\nCMS dev サーバーが停止しました。\n";
        echo "App サーバーは継続中 (Ctrl+C で停止)\n\n";
        // Keep app running even if CMS dies
        while (proc_get_status($app)['running']) {
            usleep(300000);
        }
        break;
    }
    usleep(300000);
}

proc_close($app);
proc_close($cms);
