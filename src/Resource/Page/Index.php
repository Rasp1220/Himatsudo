<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceObject;
use Himatsudo\Repository\ArticleRepository;
use Himatsudo\Repository\CategoryRepository;

class Index extends ResourceObject
{
    public function __construct(
        private readonly ArticleRepository  $articleRepository,
        private readonly CategoryRepository $categoryRepository,
    ) {}

    public function onGet(): static
    {
        $categories = $this->categoryRepository->findAll();

        $categoriesWithArticles = [];
        foreach ($categories as $category) {
            $articles = $this->articleRepository->findLatestByCategory((int) $category['id'], 8);
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
