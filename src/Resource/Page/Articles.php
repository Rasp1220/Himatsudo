<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\ArticleInterface as ArticleServiceInterface;
use Himatsudo\Interfaces\CategoryInterface as CategoryServiceInterface;

class Articles extends ResourceObject
{
    public function __construct(
        private readonly ArticleServiceInterface  $articleService,
        private readonly CategoryServiceInterface $categoryService,
    ) {}

    public function onGet(int $page = 1, ?int $category_id = null, int $per_page = 12): static
    {
        $result = $this->articleService->getList($page, $per_page, $category_id, 'published');

        $categories = $this->categoryService->getAll();

        $currentCategory = null;
        if ($category_id !== null) {
            $currentCategory = $this->categoryService->getById($category_id);
        }

        $this->body = [
            'articles'         => $result['items'],
            'total'            => $result['total'],
            'page'             => $result['page'],
            'per_page'         => $result['per_page'],
            'last_page'        => $result['last_page'],
            'category_id'      => $category_id,
            'current_category' => $currentCategory,
            'categories'       => $categories,
            'page_title'       => $currentCategory ? (string) $currentCategory['name'] : '記事一覧',
        ];

        return $this;
    }
}
