<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceObject;
use Himatsudo\Domain\Article;
use Himatsudo\Domain\Category;
use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;

/**
 * 特定タイプのカテゴリ（blog / youtube など）の記事一覧ページ共通処理。
 */
abstract class CategoryTypeListPage extends ResourceObject
{
    /** 対象カテゴリの type 値 */
    protected const CATEGORY_TYPE = '';
    /** カテゴリ未登録時のページタイトル */
    protected const FALLBACK_TITLE = '';
    /** ページネーションのベースURL */
    protected const LIST_BASE_URL = '';

    public function __construct(
        private readonly ArticleInterface  $articleService,
        private readonly CategoryInterface $categoryService,
    ) {}

    public function onGet(int $page = 1, int $per_page = 12): static
    {
        $categories = $this->categoryService->getAll();
        $category   = $this->categoryService->getByType(static::CATEGORY_TYPE);

        $result = $category !== null
            ? $this->articleService->getList($page, $per_page, $category->id, 'published')
            : ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => $per_page, 'last_page' => 1];

        $this->body = [
            'articles'         => array_map(static fn (Article $a) => $a->toArray(), $result['items']),
            'total'            => $result['total'],
            'page'             => $result['page'],
            'per_page'         => $result['per_page'],
            'last_page'        => $result['last_page'],
            'category_id'      => $category?->id,
            'current_category' => $category?->toArray(),
            'categories'       => array_map(static fn (Category $c) => $c->toArray(), $categories),
            'page_title'       => $category ? $category->name : static::FALLBACK_TITLE,
            'list_base_url'    => static::LIST_BASE_URL,
            '_template'        => 'articles/index',
        ];

        return $this;
    }
}
