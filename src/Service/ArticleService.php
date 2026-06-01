<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use Himatsudo\Contract\Repository\ArticleRepositoryInterface;
use Himatsudo\Contract\Service\ArticleServiceInterface;

final class ArticleService implements ArticleServiceInterface
{
    public function __construct(
        private readonly ArticleRepositoryInterface $articleRepository
    ) {}

    public function getList(int $page = 1, int $perPage = 15, ?int $categoryId = null, string $status = 'published'): array
    {
        return $this->articleRepository->findAll($page, $perPage, $categoryId, $status);
    }

    public function getAdminList(int $page = 1, int $perPage = 20, ?int $categoryId = null, ?string $status = null, ?string $keyword = null): array
    {
        $result = $this->articleRepository->findAllAdmin($page, $perPage, $categoryId, $status, $keyword);
        return [
            'items'     => $result['items'],
            'total'     => $result['total'],
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) ceil($result['total'] / max(1, $perPage)),
        ];
    }

    public function getBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        return $this->articleRepository->findBySlug($slug, $publishedOnly);
    }

    public function getById(int $id): ?array
    {
        return $this->articleRepository->findById($id);
    }

    public function getLatest(int $limit = 10): array
    {
        return $this->articleRepository->findLatest($limit);
    }

    public function getLatestByCategory(int $categoryId, int $limit = 8): array
    {
        return $this->articleRepository->findLatestByCategory($categoryId, $limit);
    }

    public function create(array $data): array
    {
        $id = $this->articleRepository->create($data);
        return $this->articleRepository->findById($id) ?? [];
    }

    public function update(int $id, array $data): ?array
    {
        $this->articleRepository->update($id, $data);
        return $this->articleRepository->findById($id);
    }

    public function delete(int $id): bool
    {
        return $this->articleRepository->delete($id);
    }
}
