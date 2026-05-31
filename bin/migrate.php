#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);

// Load .env if it exists
$envFile = $root . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

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

$dirs = [
    'migrations' => $root . '/database/migrations',
    'seeds'      => $root . '/database/seeds',
];

foreach ($dirs as $label => $dir) {
    $files = glob($dir . '/*.sql');
    sort($files);

    echo "\n\033[33m▶ {$label}\033[0m\n";

    foreach ($files as $file) {
        $name = basename($file);
        try {
            $sql = file_get_contents($file);
            // Split on semicolons to execute multiple statements
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
