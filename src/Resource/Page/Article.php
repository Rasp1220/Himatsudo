<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Article extends ResourceObject
{
    public function __construct(private readonly ResourceInterface $resource) {}

    public function onGet(string $slug): static
    {
        $ro = $this->resource->get->uri('app://self/article')->withQuery(['slug' => $slug])->eager->request();

        if ($ro->code === 404) {
            $this->code = 404;
            $this->body = ['error' => '記事が見つかりません', '_template' => 'error/404'];
            return $this;
        }

        $categoryType = (string) ($ro->body['article']['category_type'] ?? '');
        $template     = $categoryType === 'youtube' ? 'articles/youtube-detail' : 'articles/detail';

        $this->body = $ro->body + ['_template' => $template];
        return $this;
    }
}
