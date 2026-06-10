<?php
declare(strict_types=1);

namespace Himatsudo\Resource\App;

use BEAR\Resource\ResourceObject;
use Himatsudo\Domain\Article as ArticleEntity;
use Himatsudo\Domain\Category;
use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;

class Articles extends ResourceObject
{
    public function __construct(
        private readonly ArticleInterface  $articleService,
        private readonly CategoryInterface $categoryService,
    ) {}

    public function onGet(int $page = 1, ?int $category_id = null, int $per_page = 12): static
    {
        $result          = $this->articleService->getList($page, $per_page, $category_id, 'published');
        $categories      = $this->categoryService->getAll();
        $currentCategory = $category_id !== null ? $this->categoryService->getById($category_id) : null;

        $this->body = [
            'articles'         => array_map(static fn (ArticleEntity $a) => $a->toArray(), $result['items']),
            'total'            => $result['total'],
            'page'             => $result['page'],
            'per_page'         => $result['per_page'],
            'last_page'        => $result['last_page'],
            'category_id'      => $category_id,
            'current_category' => $currentCategory?->toArray(),
            'categories'       => array_map(static fn (Category $c) => $c->toArray(), $categories),
            'page_title'       => $currentCategory ? $currentCategory->name : '記事一覧',
        ];

        return $this;
    }
}
