<?php
declare(strict_types=1);

namespace Himatsudo\Resource\App;

use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;

class Index extends ResourceObject
{
    public function __construct(
        private readonly ArticleInterface  $articleService,
        private readonly CategoryInterface $categoryService,
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
