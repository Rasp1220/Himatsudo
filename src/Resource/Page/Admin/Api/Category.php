<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Interfaces\CategoryInterface as CategoryServiceInterface;

class Category extends ResourceObject
{
    public function __construct(private readonly CategoryServiceInterface $categoryService)
    {
    }

    public function onGet(int $id): static
    {
        $category = $this->categoryService->getById($id);
        if ($category === null) {
            $this->code = 404;
            $this->body = ['error' => 'Category not found'];
            return $this;
        }
        $this->body = $category->toArray();
        return $this;
    }

    #[RequireAuth]
    public function onPut(int $id, ?string $name = null, ?string $slug = null, ?string $type = null, ?int $sort_order = null): static
    {
        if ($this->categoryService->getById($id) === null) {
            $this->code = 404;
            $this->body = ['error' => 'Category not found'];
            return $this;
        }
        $data = array_filter(compact('name', 'slug', 'type', 'sort_order'), fn($v) => $v !== null);
        $this->body = $this->categoryService->update($id, $data)?->toArray();
        return $this;
    }

    #[RequireAuth]
    public function onDelete(int $id): static
    {
        if ($this->categoryService->getById($id) === null) {
            $this->code = 404;
            $this->body = ['error' => 'Category not found'];
            return $this;
        }
        $this->categoryService->delete($id);
        $this->code = 204;
        $this->body = null;
        return $this;
    }
}
