<?php

declare(strict_types=1);

namespace Himatsudo\Service;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Domain\User;
use Himatsudo\Interfaces\UserInterface;

final class UserService implements UserInterface
{
    use SqlFileTrait;
    use PaginationTrait;

    public function __construct(private readonly ExtendedPdoInterface $pdo)
    {
    }

    public function getList(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $rows   = $this->pdo->fetchAll($this->sql('users/get_list.sql'), ['limit' => $perPage, 'offset' => $offset]);
        $items  = array_map(static fn (array $row) => User::fromArray($row)->toArray(), $rows);
        $total  = (int) $this->pdo->fetchValue('SELECT COUNT(*) FROM users');
        return $this->paginate($items, $total, $page, $perPage);
    }

    public function getById(int $id): ?array
    {
        $row = $this->pdo->fetchOne($this->sql('users/get_by_id.sql'), ['id' => $id]);
        return $row ? User::fromArray($row)->toArray() : null;
    }

    public function getByEmail(string $email): ?array
    {
        $row = $this->pdo->fetchOne($this->sql('users/get_by_email.sql'), ['email' => $email]);
        return $row ?: null;
    }

    public function create(string $name, string $email, string $password, string $role = 'editor'): array
    {
        $this->pdo->perform(
            'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)',
            ['name' => $name, 'email' => $email, 'password' => $this->hashPassword($password), 'role' => $role]
        );
        return $this->getById((int) $this->pdo->lastInsertId()) ?? [];
    }

    public function update(int $id, array $data): ?array
    {
        $sets = [];
        $bind = ['id' => $id];
        foreach (['name', 'email', 'role'] as $field) {
            if (isset($data[$field])) {
                $sets[]       = "{$field} = :{$field}";
                $bind[$field] = $data[$field];
            }
        }
        if (isset($data['password'])) {
            $sets[]           = 'password = :password';
            $bind['password'] = $this->hashPassword((string) $data['password']);
        }
        if (!empty($sets)) {
            $this->pdo->perform('UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = :id', $bind);
        }
        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->pdo->perform($this->sql('users/delete.sql'), ['id' => $id])->rowCount();
    }

    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    private function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
