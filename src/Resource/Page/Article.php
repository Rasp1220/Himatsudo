<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceObject;
use Himatsudo\Contract\Service\ArticleServiceInterface;
use Himatsudo\Contract\Service\CategoryServiceInterface;

class Article extends ResourceObject
{
    public function __construct(
        private readonly ArticleServiceInterface  $articleService,
        private readonly CategoryServiceInterface $categoryService,
    ) {}

    public function onGet(string $slug): static
    {
        $article = $this->articleService->getBySlug($slug, true);

        if ($article === null) {
            $this->code = 404;
            $this->body = ['error' => '記事が見つかりません', '_template' => 'error/404'];
            return $this;
        }

        $categoryType = (string) ($article['category_type'] ?? '');
        $template     = $categoryType === 'youtube' ? 'articles/youtube-detail' : 'articles/detail';

        $categories = $this->categoryService->getAll();

        $this->body = [
            'article'    => $article,
            'categories' => $categories,
            'page_title' => (string) $article['title'],
            '_template'  => $template,
        ];

        return $this;
    }
}
