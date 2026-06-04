<?php
declare(strict_types=1);

namespace Himatsudo\Interfaces;

use Himatsudo\Annotation\SqlQuery;

interface CategoryInterface
{
    /** @return array<int, array<string, mixed>> */
    #[SqlQuery('categories/get_all.sql', [])]
    public function getAll(): array;

    /** @return array<string, mixed>|null */
    #[SqlQuery('categories/get_by_id.sql', ['id'])]
    public function getById(int $id): ?array;

    /** @return array<string, mixed>|null */
    public function getByType(string $type): ?array;

    /** @return array<string, mixed>|null */
    #[SqlQuery('categories/get_by_slug.sql', ['slug'])]
    public function getBySlug(string $slug): ?array;

    /** @return array<string, mixed> */
    public function create(string $name, string $slug, string $type = 'custom', int $sortOrder = 0): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     */
    public function update(int $id, array $data): ?array;

    #[SqlQuery('categories/delete.sql', ['id'])]
    public function delete(int $id): bool;
}
