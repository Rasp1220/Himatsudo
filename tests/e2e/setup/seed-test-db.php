<?php
/**
 * Seeds the test database with a known admin user for E2E tests.
 * Run after migrations: php tests/e2e/setup/seed-test-db.php
 */
declare(strict_types=1);

$dsn      = $_ENV['DB_DSN']      ?? getenv('DB_DSN')      ?: 'mysql:host=127.0.0.1;dbname=himatsudo_test;charset=utf8mb4';
$user     = $_ENV['DB_USER']     ?? getenv('DB_USER')     ?: 'root';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

$pdo = new PDO($dsn, $user ?: null, $password ?: null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$pdo->exec('DELETE FROM articles');
$pdo->exec('DELETE FROM refresh_tokens');
$pdo->exec('DELETE FROM users');
$pdo->exec('DELETE FROM categories');

$hash = password_hash('test-password-e2e', PASSWORD_BCRYPT, ['cost' => 10]);
$pdo->prepare(
    'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)'
)->execute([
    'name'     => 'E2E Admin',
    'email'    => 'e2e-admin@example.com',
    'password' => $hash,
    'role'     => 'admin',
]);

$pdo->prepare(
    'INSERT INTO categories (name, slug, type, sort_order) VALUES (:name, :slug, :type, :sort_order)'
)->execute([
    'name'       => 'General',
    'slug'       => 'general',
    'type'       => 'normal',
    'sort_order' => 0,
]);

echo "Test DB seeded.\n";
echo "  Admin: e2e-admin@example.com / test-password-e2e\n";
