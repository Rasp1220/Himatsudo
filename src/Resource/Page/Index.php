<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;

class Index extends ResourceObject
{
    public function __construct(private readonly ExtendedPdoInterface $pdo)
    {
    }

    public function onGet(): static
    {
        $latestArticles = $this->pdo->fetchAll(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.eye_catch_image, a.youtube_thumbnail,
                    a.published_at, c.name AS category_name, c.slug AS category_slug, c.type AS category_type
             FROM articles a
             LEFT JOIN categories c ON c.id = a.category_id
             WHERE a.status = 'published'
             ORDER BY a.published_at DESC, a.created_at DESC
             LIMIT 10"
        );

        $categories = $this->pdo->fetchAll(
            'SELECT id, name, slug, type FROM categories ORDER BY sort_order ASC, id ASC'
        );

        $this->body = [
            'latest_articles' => $latestArticles,
            'categories'      => $categories,
            'page_title'      => 'ホーム',
        ];

        return $this;
    }
}
