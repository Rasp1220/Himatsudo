<?php
declare(strict_types=1);

namespace Himatsudo\Contract\Repository;

interface CategoryRepositoryInterface
{
    /** @return array<int, array<string, mixed>> */
    public function findAll(): array;

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array;

    public function create(string $name, string $slug, string $type = 'custom', int $sortOrder = 0): int;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
