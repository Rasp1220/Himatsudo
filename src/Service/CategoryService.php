<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use Himatsudo\Contract\Repository\CategoryRepositoryInterface;
use Himatsudo\Contract\Service\CategoryServiceInterface;

final class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {}

    public function getAll(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function getById(int $id): ?array
    {
        return $this->categoryRepository->findById($id);
    }

    public function create(string $name, string $slug, string $type = 'custom', int $sortOrder = 0): array
    {
        $id = $this->categoryRepository->create($name, $slug, $type, $sortOrder);
        return $this->categoryRepository->findById($id) ?? [];
    }

    public function update(int $id, array $data): ?array
    {
        $this->categoryRepository->update($id, $data);
        return $this->categoryRepository->findById($id);
    }

    public function delete(int $id): bool
    {
        return $this->categoryRepository->delete($id);
    }
}
