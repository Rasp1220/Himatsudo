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

// ── ブートストラップ ─────────────────────────────────────────────────────────
// schema_migrations が空のとき（トラッキング導入前に適用済みのDBに初回実行する場合）、
// INFORMATION_SCHEMA でスキーマを検査し、既に存在するテーブル・カラムを持つ
// マイグレーションを自動的に「適用済み」として記録する。
// これにより、既存DBで「Duplicate column / Table already exists」で止まらずに
// 未適用の新しいマイグレーションだけを実行できる。
if (empty($applied)) {
    $colExists = static function (string $table, string $column) use ($pdo): bool {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->execute([$table, $column]);
        return (bool) $stmt->fetchColumn();
    };

    $tblExists = static function (string $table) use ($pdo): bool {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?'
        );
        $stmt->execute([$table]);
        return (bool) $stmt->fetchColumn();
    };

    // 各マイグレーションファイルが適用済みかどうかを判定するクロージャのマップ。
    // 追加したマイグレーションファイルに対応するエントリをここに追加すること。
    $legacyMap = [
        '001_create_users_table.sql'          => fn () => $tblExists('users'),
        '002_create_categories_table.sql'     => fn () => $tblExists('categories'),
        '003_create_articles_table.sql'       => fn () => $tblExists('articles'),
        '004_create_refresh_tokens_table.sql' => fn () => $tblExists('refresh_tokens'),
        '005_add_related_articles.sql'        => fn () => $colExists('articles', 'related_article_ids'),
        '006_add_user_profile.sql'            => fn () => $colExists('users', 'avatar'),
        '007_add_user_sns.sql'                => fn () => $colExists('users', 'instagram_url'),
    ];

    $insertStmt  = $pdo->prepare('INSERT IGNORE INTO schema_migrations (filename) VALUES (?)');
    $bootstrapped = [];
    foreach ($legacyMap as $filename => $check) {
        if ($check()) {
            $insertStmt->execute([$filename]);
            $applied[$filename] = true;
            $bootstrapped[]     = $filename;
        }
    }

    if (!empty($bootstrapped)) {
        echo "\n\033[33m▶ 既存スキーマを検出して記録しました（初回トラッキング）\033[0m\n";
        foreach ($bootstrapped as $f) {
            echo "  \033[90m•\033[0m {$f}\n";
        }
    }
}

// ── migrations（適用済みはスキップ、未適用のみ実行して記録） ──
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
            $pdo->exec($stmt);
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
            $pdo->exec($stmt);
        }
        echo "  \033[32m✓\033[0m {$name}\n";
    } catch (PDOException $e) {
        echo "  \033[31m✗\033[0m {$name}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\n\033[32m完了しました。\033[0m\n";
