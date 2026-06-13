<?php

declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Interfaces\UserInterface;

class Author extends ResourceObject
{
    public function __construct(
        private readonly UserInterface     $userService,
        private readonly ArticleInterface  $articleService,
        private readonly CategoryInterface $categoryService,
    ) {
    }

    public function onGet(int $id, int $page = 1, int $per_page = 12): static
    {
        $author = $this->userService->getPublicById($id);

        if ($author === null) {
            $this->code = 404;
            $this->body = ['error' => '運営が見つかりません', '_template' => 'error/404'];
            return $this;
        }

        $result = $this->articleService->getListByAuthor($id, $page, $per_page, 'published');

        $this->body = [
            'author'        => $author,
            'articles'      => $result['items'],
            'total'         => $result['total'],
            'page'          => $result['page'],
            'per_page'      => $result['per_page'],
            'last_page'     => $result['last_page'],
            'categories'    => $this->categoryService->getAll(),
            'page_title'    => (string) $author['name'],
            'list_base_url' => '/author/' . $id,
            '_template'     => 'author/index',
        ];

        return $this;
    }
}
