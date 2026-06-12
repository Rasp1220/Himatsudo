<?php

declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Interfaces\CategoryInterface as CategoryServiceInterface;

class Categories extends ResourceObject
{
    public function __construct(private readonly CategoryServiceInterface $categoryService)
    {
    }

    public function onGet(): static
    {
        $this->body = $this->categoryService->getAll();
        return $this;
    }

    #[RequireAuth]
    public function onPost(string $name, string $slug, string $type = 'custom', int $sort_order = 0): static
    {
        $validTypes = ['normal', 'blog', 'youtube', 'custom'];
        if (!in_array($type, $validTypes, true)) {
            $this->code = 422;
            $this->body = ['error' => 'Invalid type'];
            return $this;
        }
        $this->code = 201;
        $this->body = $this->categoryService->create($name, $slug, $type, $sort_order);
        return $this;
    }
}
