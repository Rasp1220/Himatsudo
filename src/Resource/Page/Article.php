<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\ArticleInterface;

class Article extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource,
        private readonly ArticleInterface $articleService,
    ) {}

    public function onGet(string $slug): static
    {
        $ro = $this->resource->get->uri('app://self/article')->withQuery(['slug' => $slug])->eager->request();

        if ($ro->code === 404) {
            $this->code = 404;
            $this->body = ['error' => '記事が見つかりません', '_template' => 'error/404'];
            return $this;
        }

        $article      = $ro->body['article'] ?? [];
        $categoryType = (string) ($article['category_type'] ?? '');
        $template     = $categoryType === 'youtube' ? 'articles/youtube-detail' : 'articles/detail';

        $prevNext = ['prev' => null, 'next' => null];
        if (!empty($article['id']) && !empty($article['published_at'])) {
            $prevNext = $this->articleService->getPrevNext(
                (int) $article['id'],
                (string) $article['published_at']
            );
        }

        $this->body = $ro->body + $prevNext + ['_template' => $template];
        return $this;
    }
}
