<?php
declare(strict_types=1);

namespace Himatsudo\Resource\App;

use BEAR\Resource\ResourceObject;
use Himatsudo\Domain\Article;
use Himatsudo\Domain\Category;
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
        $toArray    = static fn (Article|Category $entity) => $entity->toArray();
        $categories = $this->categoryService->getAll();

        $categoriesWithArticles = [];
        foreach ($categories as $category) {
            $articles = $this->articleService->getLatestByCategory($category->id, 20);
            if ($articles !== []) {
                $categoriesWithArticles[] = [
                    'category' => $category->toArray(),
                    'articles' => array_map($toArray, $articles),
                ];
            }
        }

        $this->body = [
            'latest_articles'          => array_map($toArray, $this->articleService->getLatest(20)),
            'categories_with_articles' => $categoriesWithArticles,
            'categories'               => array_map($toArray, $categories),
            'page_title'               => 'ホーム',
        ];

        return $this;
    }
}
