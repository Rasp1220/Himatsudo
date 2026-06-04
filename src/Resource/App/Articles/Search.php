<?php
declare(strict_types=1);

namespace Himatsudo\Resource\App\Articles;

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
                'items'     => [],
                'total'     => 0,
                'page'      => 1,
                'per_page'  => 15,
                'last_page' => 1,
                'keyword'   => '',
            ];
            return $this;
        }
        $this->body = $this->articleService->search($q, $page);
        return $this;
    }
}
