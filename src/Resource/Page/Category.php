<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\CategoryInterface;

class Category extends ResourceObject
{
    public function __construct(
        private readonly ResourceInterface $resource,
        private readonly CategoryInterface $categoryService,
    ) {}

    public function onGet(string $slug, int $page = 1, int $per_page = 12): static
    {
        $category = $this->categoryService->getBySlug($slug);
        if ($category === null) {
            $this->code = 404;
            $this->body = ['error' => 'カテゴリが見つかりません', '_template' => 'error/404'];
            return $this;
        }

        $ro = $this->resource->get
            ->uri('app://self/articles')
            ->withQuery(['page' => $page, 'per_page' => $per_page, 'category_id' => $category['id']])
            ->eager->request();

        $this->body                = $ro->body;
        $this->body['_template']   = 'articles/index';
        $this->body['category_slug'] = $slug;
        return $this;
    }
}
