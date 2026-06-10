<?php
declare(strict_types=1);

namespace Himatsudo\Interfaces;

use Himatsudo\Domain\Article;

interface ArticleInterface
{
    /**
     * @return array{items: list<Article>, total: int, page: int, per_page: int, last_page: int}
     */
    public function getList(int $page = 1, int $perPage = 15, ?int $categoryId = null, string $status = 'published'): array;

    /**
     * @return array{items: list<Article>, total: int, page: int, per_page: int, last_page: int}
     */
    public function getAdminList(int $page = 1, int $perPage = 20, ?int $categoryId = null, ?string $status = null, ?string $keyword = null): array;

    public function getBySlug(string $slug, bool $publishedOnly = true): ?Article;

    public function getById(int $id): ?Article;

    /** @return list<Article> */
    public function getLatest(int $limit = 10): array;

    /** @return list<Article> */
    public function getLatestByCategory(int $categoryId, int $limit = 8): array;

    /** @param array<string, mixed> $data */
    public function create(array $data): Article;

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): ?Article;

    public function delete(int $id): bool;
}
