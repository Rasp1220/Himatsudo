#!/usr/bin/env php
<?php
declare(strict_types=1);

$root  = dirname(__DIR__);
$fresh = in_array('--fresh', $argv, true);

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

if ($fresh) {
    echo "\033[33m⚠ --fresh: テーブルを全削除してから再作成します\033[0m\n";
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach (['refresh_tokens', 'articles', 'categories', 'users', 'schema_migrations'] as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
        echo "  dropped {$table}\n";
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
}

// 適用済みマイグレーションを記録するテーブル。
// これにより各マイグレーションは一度だけ実行され、再実行で
// 「Duplicate column / Table already exists」で止まらなくなる。
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        filename   VARCHAR(255) NOT NULL,
        applied_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (filename)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$applied = array_flip(
    $pdo->query('SELECT filename FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN)
);

// 既存DBに対して初めてトラッキングを導入する場合に備え、
// 「既に存在する」系のエラーは冪等なものとして読み飛ばす。
$benignCodes = [
    1050, // Table already exists
    1060, // Duplicate column name
    1061, // Duplicate key name
    1062, // Duplicate entry
    1091, // Can't DROP ...; check that it exists
    1022, // Duplicate key
    1826, // Duplicate foreign key constraint name
];

$execStmt = static function (string $stmt) use ($pdo, $benignCodes): void {
    try {
        $pdo->exec($stmt);
    } catch (PDOException $e) {
        $code = (int) ($e->errorInfo[1] ?? 0);
        if (!in_array($code, $benignCodes, true)) {
            throw $e;
        }
        // 冪等なエラー（既に適用済み）は無視して継続する。
    }
};

// ── migrations（適用済みはスキップ、適用後に記録） ──
echo "\n\033[33m▶ migrations\033[0m\n";
$files = glob($root . '/var/db/migrations/*.sql');
sort($files);
foreach ($files as $file) {
    $name = basename($file);
    if (isset($applied[$name])) {
        echo "  \033[90m•\033[0m {$name} (適用済み)\n";
        continue;
    }
    try {
        $sql = (string) file_get_contents($file);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            $execStmt($stmt);
        }
        $pdo->prepare('INSERT IGNORE INTO schema_migrations (filename) VALUES (?)')->execute([$name]);
        echo "  \033[32m✓\033[0m {$name}\n";
    } catch (PDOException $e) {
        echo "  \033[31m✗\033[0m {$name}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// ── seeds（冪等。毎回実行する） ──
echo "\n\033[33m▶ seeds\033[0m\n";
$files = glob($root . '/var/db/seeds/*.sql');
sort($files);
foreach ($files as $file) {
    $name = basename($file);
    try {
        $sql = (string) file_get_contents($file);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            $execStmt($stmt);
        }
        echo "  \033[32m✓\033[0m {$name}\n";
    } catch (PDOException $e) {
        echo "  \033[31m✗\033[0m {$name}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\n\033[32m完了しました。\033[0m\n";
