<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Articles extends ResourceObject
{
    public function __construct(private readonly ResourceInterface $resource) {}

    public function onGet(int $page = 1, ?int $category_id = null, int $per_page = 12): static
    {
        $query = array_filter(['page' => $page, 'category_id' => $category_id, 'per_page' => $per_page], fn($v) => $v !== null);
        $ro    = $this->resource->get->uri('app://self/articles')->withQuery($query)->eager->request();

        $this->body = $ro->body;
        return $this;
    }
}
