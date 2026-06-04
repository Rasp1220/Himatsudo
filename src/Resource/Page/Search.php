<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Search extends ResourceObject
{
    public function __construct(private readonly ResourceInterface $resource) {}

    public function onGet(string $q = '', int $page = 1): static
    {
        $ro = $this->resource->get
            ->uri('app://self/articles/search')
            ->withQuery(['q' => $q, 'page' => $page])
            ->eager->request();

        $this->body = $ro->body + ['_template' => 'search'];
        return $this;
    }
}
