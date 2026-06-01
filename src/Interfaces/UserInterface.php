<?php
declare(strict_types=1);

namespace Himatsudo\Interfaces;

use Himatsudo\Annotation\SqlQuery;

interface UserInterface
{
    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int, last_page: int}
     */
    #[SqlQuery('users/get_list.sql', ['limit', 'offset'])]
    public function getList(int $page = 1, int $perPage = 20): array;

    /** @return array<string, mixed>|null */
    #[SqlQuery('users/get_by_id.sql', ['id'])]
    public function getById(int $id): ?array;

    /** @return array<string, mixed>|null */
    #[SqlQuery('users/get_by_email.sql', ['email'])]
    public function getByEmail(string $email): ?array;

    /** @return array<string, mixed> */
    public function create(string $name, string $email, string $password, string $role = 'editor'): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     */
    public function update(int $id, array $data): ?array;

    #[SqlQuery('users/delete.sql', ['id'])]
    public function delete(int $id): bool;

    public function verifyPassword(string $plain, string $hash): bool;
}
