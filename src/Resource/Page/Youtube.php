<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;

class Youtube extends ResourceObject
{
    public function __construct(
        private readonly ArticleInterface  $articleService,
        private readonly CategoryInterface $categoryService,
    ) {}

    public function onGet(int $page = 1, int $per_page = 12): static
    {
        $categories      = $this->categoryService->getAll();
        $youtubeCategory = null;
        foreach ($categories as $cat) {
            if (($cat['type'] ?? '') === 'youtube') {
                $youtubeCategory = $cat;
                break;
            }
        }

        $result = $youtubeCategory !== null
            ? $this->articleService->getList($page, $per_page, (int) $youtubeCategory['id'], 'published')
            : ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => $per_page, 'last_page' => 1];

        $this->body = [
            'articles'         => $result['items'],
            'total'            => $result['total'],
            'page'             => $result['page'],
            'per_page'         => $result['per_page'],
            'last_page'        => $result['last_page'],
            'category_id'      => $youtubeCategory ? (int) $youtubeCategory['id'] : null,
            'current_category' => $youtubeCategory,
            'categories'       => $categories,
            'page_title'       => $youtubeCategory ? (string) $youtubeCategory['name'] : 'YouTube',
            'list_base_url'    => '/youtube',
            '_template'        => 'articles/index',
        ];

        return $this;
    }
}
