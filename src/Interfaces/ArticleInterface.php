<?php
declare(strict_types=1);

namespace Himatsudo\Interfaces;

use Himatsudo\Annotation\SqlQuery;

interface ArticleInterface
{
    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int, last_page: int}
     */
    #[SqlQuery('articles/get_list_base.sql', ['status', 'limit', 'offset', 'category_id'])]
    public function getList(int $page = 1, int $perPage = 15, ?int $categoryId = null, string $status = 'published'): array;

    /**
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int, last_page: int}
     */
    #[SqlQuery('articles/get_admin_list_base.sql', ['limit', 'offset', 'status', 'category_id', 'keyword'])]
    public function getAdminList(int $page = 1, int $perPage = 20, ?int $categoryId = null, ?string $status = null, ?string $keyword = null): array;

    /** @return array<string, mixed>|null */
    #[SqlQuery('articles/get_by_slug.sql', ['slug'])]
    public function getBySlug(string $slug, bool $publishedOnly = true): ?array;

    /** @return array<string, mixed>|null */
    #[SqlQuery('articles/get_by_id.sql', ['id'])]
    public function getById(int $id): ?array;

    /** @return array<int, array<string, mixed>> */
    #[SqlQuery('articles/get_latest.sql', ['limit'])]
    public function getLatest(int $limit = 10): array;

    /** @return array<int, array<string, mixed>> */
    #[SqlQuery('articles/get_latest_by_category.sql', ['category_id', 'limit'])]
    public function getLatestByCategory(int $categoryId, int $limit = 8): array;

    /** @return array<int, array<string, mixed>> */
    #[SqlQuery('articles/get_latest_exclude_type.sql', ['exclude_type', 'limit'])]
    public function getLatestExcludeType(string $excludeType, int $limit = 20): array;

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

    #[SqlQuery('articles/delete.sql', ['id'])]
    public function delete(int $id): bool;
}
