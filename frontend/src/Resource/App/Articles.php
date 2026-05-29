<?php
declare(strict_types=1);

namespace Himatsudo\Frontend\Resource\App;

use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;

class Articles extends ResourceObject
{
    public function __construct(private readonly ExtendedPdoInterface $pdo)
    {
    }

    public function onGet(int $page = 1, ?int $category_id = null, int $per_page = 12): static
    {
        $offset = ($page - 1) * $per_page;
        $where  = "WHERE a.status = 'published'";
        $bind   = ['limit' => $per_page, 'offset' => $offset];

        if ($category_id !== null) {
            $where .= ' AND a.category_id = :category_id';
            $bind['category_id'] = $category_id;
        }

        $articles = $this->pdo->fetchAll(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.eye_catch_image, a.youtube_thumbnail,
                    a.published_at, c.name AS category_name, c.slug AS category_slug, c.type AS category_type
             FROM articles a
             LEFT JOIN categories c ON c.id = a.category_id
             {$where}
             ORDER BY a.published_at DESC, a.created_at DESC
             LIMIT :limit OFFSET :offset",
            $bind
        );

        $countBind  = array_filter($bind, fn($k) => !in_array($k, ['limit', 'offset']), ARRAY_FILTER_USE_KEY);
        $countWhere = str_replace(' a.category_id', ' category_id', $where);
        $total      = (int) $this->pdo->fetchValue(
            "SELECT COUNT(*) FROM articles {$countWhere}",
            $countBind
        );

        $categories = $this->pdo->fetchAll(
            'SELECT id, name, slug, type FROM categories ORDER BY sort_order ASC, id ASC'
        );

        $currentCategory = null;
        if ($category_id !== null) {
            $currentCategory = $this->pdo->fetchOne(
                'SELECT id, name, slug, type FROM categories WHERE id = :id LIMIT 1',
                ['id' => $category_id]
            ) ?: null;
        }

        $this->body = [
            'articles'         => $articles,
            'total'            => $total,
            'page'             => $page,
            'per_page'         => $per_page,
            'last_page'        => (int) ceil($total / $per_page),
            'category_id'      => $category_id,
            'current_category' => $currentCategory,
            'categories'       => $categories,
            'page_title'       => $currentCategory ? (string) $currentCategory['name'] : '記事一覧',
        ];

        return $this;
    }
}
