<?php
declare(strict_types=1);

namespace Himatsudo\Interfaces;

interface ArticleInterface
{
    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int, last_page: int}
     */
    public function getList(int $page = 1, int $perPage = 15, ?int $categoryId = null, string $status = 'published'): array;

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int, last_page: int}
     */
    public function getAdminList(int $page = 1, int $perPage = 20, ?int $categoryId = null, ?string $status = null, ?string $keyword = null): array;

    /** @return array<string, mixed>|null */
    public function getBySlug(string $slug, bool $publishedOnly = true): ?array;

    /** @return array<string, mixed>|null */
    public function getById(int $id): ?array;

    /** @return array<int, array<string, mixed>> */
    public function getLatest(int $limit = 10): array;

    /** @return array<int, array<string, mixed>> */
    public function getLatestByCategory(int $categoryId, int $limit = 8): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(array $data): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>|null
     */
    public function update(int $id, array $data): ?array;

    public function delete(int $id): bool;
}
