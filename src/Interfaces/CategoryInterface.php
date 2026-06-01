<?php
declare(strict_types=1);

namespace Himatsudo\Interfaces;

interface CategoryInterface
{
    /** @return array<int, array<string, mixed>> */
    public function getAll(): array;

    /** @return array<string, mixed>|null */
    public function getById(int $id): ?array;

    /** @return array<string, mixed> */
    public function create(string $name, string $slug, string $type = 'custom', int $sortOrder = 0): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     */
    public function update(int $id, array $data): ?array;

    public function delete(int $id): bool;
}
