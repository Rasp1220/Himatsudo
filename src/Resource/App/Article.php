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

        $publishedAt = (string) ($article['published_at'] ?? '');
        $currentId   = (int) $article['id'];
        $categoryId  = (int) ($article['category_id'] ?? 0);
        $relatedIds  = is_array($article['related_article_ids'] ?? null)
            ? $article['related_article_ids']
            : [];

        $this->body = [
            'article'          => $article,
            'categories'       => $this->categoryService->getAll(),
            'page_title'       => (string) $article['title'],
            'prev_article'     => ($publishedAt && $categoryId) ? $this->articleService->getPrev($publishedAt, $currentId, $categoryId) : null,
            'next_article'     => ($publishedAt && $categoryId) ? $this->articleService->getNext($publishedAt, $currentId, $categoryId) : null,
            'related_articles' => $this->articleService->getByIds($relatedIds),
        ];

        return $this;
    }
}
