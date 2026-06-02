<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Interfaces\UserInterface;

final class UserService implements UserInterface
{
    private string $sqlDir;

    public function __construct(private readonly ExtendedPdoInterface $pdo)
    {
        $this->sqlDir = dirname(__DIR__) . '/sql/';
    }

    private function sql(string $file): string
    {
        return (string) file_get_contents($this->sqlDir . $file);
    }

    public function getList(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $items  = $this->pdo->fetchAll($this->sql('users/get_list.sql'), ['limit' => $perPage, 'offset' => $offset]);
        $total  = (int) $this->pdo->fetchValue('SELECT COUNT(*) FROM users');
        return [
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) ceil($total / max(1, $perPage)),
        ];
    }

    public function getById(int $id): ?array
    {
        $row = $this->pdo->fetchOne($this->sql('users/get_by_id.sql'), ['id' => $id]);
        return $row ?: null;
    }

    public function getByEmail(string $email): ?array
    {
        $row = $this->pdo->fetchOne($this->sql('users/get_by_email.sql'), ['email' => $email]);
        return $row ?: null;
    }

    public function create(string $name, string $email, string $password, string $role = 'editor'): array
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->pdo->perform(
            'INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)',
            ['name' => $name, 'email' => $email, 'password' => $hash, 'role' => $role]
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
            $bind['password'] = password_hash((string) $data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
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
}
