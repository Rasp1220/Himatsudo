<?php
declare(strict_types=1);

namespace Himatsudo\Contract\Repository;

interface UserRepositoryInterface
{
    /** @return array<string, mixed>|null */
    public function findByEmail(string $email): ?array;

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array;

    /** @return array<int, array<string, mixed>> */
    public function findAll(int $page = 1, int $perPage = 20): array;

    public function count(): int;

    public function create(string $name, string $email, string $password, string $role = 'editor'): int;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function verifyPassword(string $plain, string $hash): bool;
}
