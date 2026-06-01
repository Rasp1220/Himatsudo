<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\ArticleInterface as ArticleServiceInterface;
use Himatsudo\Interfaces\CategoryInterface as CategoryServiceInterface;

class Index extends ResourceObject
{
    public function __construct(
        private readonly ArticleServiceInterface  $articleService,
        private readonly CategoryServiceInterface $categoryService,
    ) {}

    public function onGet(): static
    {
        $categories = $this->categoryService->getAll();

        $categoriesWithArticles = [];
        foreach ($categories as $category) {
            $articles = $this->articleService->getLatestByCategory((int) $category['id'], 8);
            if (!empty($articles)) {
                $categoriesWithArticles[] = [
                    'category' => $category,
                    'articles' => $articles,
                ];
            }
        }

        $this->body = [
            'categories_with_articles' => $categoriesWithArticles,
            'page_title'               => 'ホーム',
        ];

        return $this;
    }
}
