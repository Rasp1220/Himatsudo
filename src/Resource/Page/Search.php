<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\ArticleInterface;

class Search extends ResourceObject
{
    public function __construct(private readonly ArticleInterface $articleService) {}

    public function onGet(string $q = '', int $page = 1): static
    {
        $q = trim($q);

        if ($q === '') {
            $this->body = [
                'q'         => '',
                'items'     => [],
                'total'     => 0,
                'page'      => 1,
                'last_page' => 1,
                '_template' => 'search/index',
            ];
            return $this;
        }

        $result = $this->articleService->search($q, $page, 12);

        $this->body = [
            'q'         => $q,
            'items'     => $result['items'],
            'total'     => $result['total'],
            'page'      => $result['page'],
            'last_page' => $result['last_page'],
            '_template' => 'search/index',
        ];
        return $this;
    }
}
