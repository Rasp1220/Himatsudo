#!/usr/bin/env php
<?php
declare(strict_types=1);

require __DIR__ . '/load-env.php';

$root  = dirname(__DIR__);
$fresh = in_array('--fresh', $argv, true);

loadDotEnv($root);

$dsn      = $_ENV['DB_DSN']      ?? 'mysql:host=localhost;dbname=himatsudo;charset=utf8mb4';
$user     = $_ENV['DB_USER']     ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    echo "\033[31m[ERROR] DB接続失敗: " . $e->getMessage() . "\033[0m\n";
    exit(1);
}

if ($fresh) {
    echo "\033[33m⚠ --fresh: テーブルを全削除してから再作成します\033[0m\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach (['refresh_tokens', 'articles', 'categories', 'users'] as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        echo "  dropped {$table}\n";
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
}

$dirs = [
    'migrations' => $root . '/var/db/migrations',
    'seeds'      => $root . '/var/db/seeds',
];

foreach ($dirs as $label => $dir) {
    $files = glob($dir . '/*.sql');
    sort($files);

    echo "\n\033[33m▶ {$label}\033[0m\n";

    foreach ($files as $file) {
        $name = basename($file);
        try {
            $sql = file_get_contents($file);
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                $pdo->exec($stmt);
            }
            echo "  \033[32m✓\033[0m {$name}\n";
        } catch (PDOException $e) {
            echo "  \033[31m✗\033[0m {$name}: " . $e->getMessage() . "\n";
            exit(1);
        }
    }
}

echo "\n\033[32m完了しました。\033[0m\n";
