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
    ) {
    }

    public function onGet(string $slug): static
    {
        $article = $this->articleService->getBySlug($slug, true);

        if ($article === null) {
            $this->code = 404;
            $this->body = ['error' => '記事が見つかりません'];
            return $this;
        }

        $prevNext = ['prev' => null, 'next' => null];
        if (!empty($article['id']) && !empty($article['published_at'])) {
            $prevNext = $this->articleService->getPrevNext(
                (int) $article['id'],
                (string) $article['published_at']
            );
        }

        $relatedArticles = [];
        if (!empty($article['related_article_ids'])) {
            $ids = json_decode((string) $article['related_article_ids'], true);
            if (is_array($ids) && !empty($ids)) {
                $relatedArticles = $this->articleService->getByIds(
                    array_slice(array_map('intval', $ids), 0, 3)
                );
            }
        }

        $this->body = [
            'article'          => $article,
            'prev'             => $prevNext['prev'],
            'next'             => $prevNext['next'],
            'categories'       => $this->categoryService->getAll(),
            'page_title'       => (string) $article['title'],
            'related_articles' => $relatedArticles,
        ];

        return $this;
    }
}
