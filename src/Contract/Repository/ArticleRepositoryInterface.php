<?php
declare(strict_types=1);

namespace Himatsudo\Contract\Repository;

interface ArticleRepositoryInterface
{
    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int, last_page: int}
     */
    public function findAll(int $page = 1, int $perPage = 15, ?int $categoryId = null, string $status = 'published'): array;

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int}
     */
    public function findAllAdmin(int $page = 1, int $perPage = 20, ?int $categoryId = null, ?string $status = null, ?string $keyword = null): array;

    /** @return array<string, mixed>|null */
    public function findBySlug(string $slug, bool $publishedOnly = true): ?array;

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array;

    /** @return array<int, array<string, mixed>> */
    public function findLatest(int $limit = 10): array;

    /** @return array<int, array<string, mixed>> */
    public function findLatestByCategory(int $categoryId, int $limit = 8): array;

    /** @param array<string, mixed> $data */
    public function create(array $data): int;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}
