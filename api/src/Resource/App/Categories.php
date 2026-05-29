<?php
declare(strict_types=1);

namespace Himatsudo\Api\Resource\App;

use BEAR\Resource\ResourceObject;
use Himatsudo\Api\Annotation\RequireAuth;
use Himatsudo\Api\Repository\CategoryRepository;

class Categories extends ResourceObject
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    public function onGet(): static
    {
        $this->body = $this->categoryRepository->findAll();
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
        $id = $this->categoryRepository->create($name, $slug, $type, $sort_order);
        $this->code = 201;
        $this->body = $this->categoryRepository->findById($id);
        return $this;
    }
}
