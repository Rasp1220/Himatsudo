<?php
declare(strict_types=1);

namespace Himatsudo\Api\Repository;

use Aura\Sql\ExtendedPdoInterface;
use PDO;

final class UserRepository
{
    public function __construct(private readonly ExtendedPdoInterface $pdo)
    {
    }

    /** @return array<string, mixed> */
    public function findByEmail(string $email): ?array
    {
        $sql = 'SELECT id, name, email, password, role, created_at, updated_at FROM users WHERE email = :email LIMIT 1';
        $row = $this->pdo->fetchOne($sql, ['email' => $email]);
        return $row ?: null;
    }

    /** @return array<string, mixed> */
    public function findById(int $id): ?array
    {
        $sql = 'SELECT id, name, email, role, created_at, updated_at FROM users WHERE id = :id LIMIT 1';
        $row = $this->pdo->fetchOne($sql, ['id' => $id]);
        return $row ?: null;
    }

    /**
     * @param array<int, mixed> $bindings
     * @return array<int, array<string, mixed>>
     */
    public function findAll(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = 'SELECT id, name, email, role, created_at, updated_at FROM users ORDER BY id DESC LIMIT :limit OFFSET :offset';
        return $this->pdo->fetchAll($sql, ['limit' => $perPage, 'offset' => $offset]);
    }

    public function count(): int
    {
        return (int) $this->pdo->fetchValue('SELECT COUNT(*) FROM users');
    }

    public function create(string $name, string $email, string $password, string $role = 'editor'): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $sql  = 'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)';
        $this->pdo->perform($sql, ['name' => $name, 'email' => $email, 'password' => $hash, 'role' => $role]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        $bind = ['id' => $id];
        foreach (['name', 'email', 'role'] as $field) {
            if (isset($data[$field])) {
                $sets[] = "{$field} = :{$field}";
                $bind[$field] = $data[$field];
            }
        }
        if (isset($data['password'])) {
            $sets[] = 'password = :password';
            $bind['password'] = password_hash((string) $data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }
        if (empty($sets)) {
            return false;
        }
        $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id';
        return (bool) $this->pdo->perform($sql, $bind)->rowCount();
    }

    public function delete(int $id): bool
    {
        return (bool) $this->pdo->perform('DELETE FROM users WHERE id = :id', ['id' => $id])->rowCount();
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }
}
