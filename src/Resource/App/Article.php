<?php
declare(strict_types=1);

namespace Himatsudo\Resource\App;

use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;

class Article extends ResourceObject
{
    public function __construct(
        private readonly ArticleInterface  $articleService,
        private readonly CategoryInterface $categoryService,
    ) {}

    public function onGet(string $slug): static
    {
        $article = $this->articleService->getBySlug($slug, true);

        if ($article === null) {
            $this->code = 404;
            $this->body = ['error' => '記事が見つかりません'];
            return $this;
        }

        $this->body = [
            'article'    => $article,
            'categories' => $this->categoryService->getAll(),
            'page_title' => (string) $article['title'],
        ];

        return $this;
    }
}
