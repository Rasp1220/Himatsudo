<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public function __construct(private readonly ResourceInterface $resource) {}

    public function onGet(): static
    {
        $ro = $this->resource->get->uri('app://self/index')->eager->request();

        $this->body = $ro->body + ['_template' => 'index'];
        return $this;
    }
}
