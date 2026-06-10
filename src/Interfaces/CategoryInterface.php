<?php
declare(strict_types=1);

namespace Himatsudo\Interfaces;

use Himatsudo\Domain\Category;

interface CategoryInterface
{
    /** @return list<Category> */
    public function getAll(): array;

    public function getById(int $id): ?Category;

    public function getByType(string $type): ?Category;

    public function getBySlug(string $slug): ?Category;

    public function create(string $name, string $slug, string $type = 'custom', int $sortOrder = 0): Category;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ?Category;

    public function delete(int $id): bool;
}
