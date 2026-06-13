<?php

declare(strict_types=1);

namespace Himatsudo\Resource\App;

use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Interfaces\UserInterface;

class Article extends ResourceObject
{
    public function __construct(
        private readonly ArticleInterface  $articleService,
        private readonly CategoryInterface $categoryService,
        private readonly UserInterface     $userService,
    ) {
    }

    public function onGet(string $slug, ?int $author_id = null): static
    {
        $article = $this->articleService->getBySlug($slug, true);

        if ($article === null) {
            $this->code = 404;
            $this->body = ['error' => '記事が見つかりません'];
            return $this;
        }

        // 運営プロフィール経由（author_id 指定）の場合は、前後の記事を
        // その運営の記事だけに絞り込み、パンくずも運営別に合わせる。
        $authorContext = null;
        if ($author_id !== null) {
            $authorContext = $this->userService->getPublicById($author_id);
        }

        $prevNext = ['prev' => null, 'next' => null];
        if (!empty($article['id']) && !empty($article['published_at'])) {
            $prevNext = $authorContext !== null
                ? $this->articleService->getPrevNextByAuthor(
                    (int) $article['id'],
                    (string) $article['published_at'],
                    (int) $author_id
                )
                : $this->articleService->getPrevNext(
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
            'author_context'   => $authorContext,
        ];

        return $this;
    }
}
