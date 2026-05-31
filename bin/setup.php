#!/usr/bin/env php
<?php
declare(strict_types=1);

$root  = dirname(__DIR__);
$isWin = PHP_OS_FAMILY === 'Windows';

function run(string $label, string $cmd, string $cwd): void
{
    echo "\033[33m▶ {$label}\033[0m\n";
    $prev = getcwd();
    chdir($cwd);
    passthru($cmd, $code);
    chdir($prev);
    if ($code !== 0) {
        echo "\033[31m✗ 失敗しました (exit {$code})\033[0m\n";
        exit(1);
    }
    echo "\033[32m✓ 完了\033[0m\n\n";
}

echo "\n=== Himatsudo セットアップ ===\n\n";

run('PHP 依存パッケージをインストール (composer update)', 'composer update', $root);
run('CMS npm パッケージをインストール (npm install)', 'npm install', $root . ($isWin ? '\cms' : '/cms'));

echo "\033[32mセットアップ完了！\033[0m\n";
echo "次のコマンドでサーバーを起動できます:\n";
echo "  composer serve\n\n";
