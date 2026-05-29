<?php
declare(strict_types=1);

namespace Himatsudo\Frontend\Resource\App;

use Aura\Sql\ExtendedPdoInterface;
use BEAR\Resource\ResourceObject;

class Article extends ResourceObject
{
    public function __construct(private readonly ExtendedPdoInterface $pdo)
    {
    }

    public function onGet(string $slug): static
    {
        $article = $this->pdo->fetchOne(
            "SELECT a.*, c.name AS category_name, c.slug AS category_slug, c.type AS category_type,
                    u.name AS author_name
             FROM articles a
             LEFT JOIN categories c ON c.id = a.category_id
             LEFT JOIN users u      ON u.id = a.author_id
             WHERE a.slug = :slug AND a.status = 'published' LIMIT 1",
            ['slug' => $slug]
        );

        if (!$article) {
            $this->code = 404;
            $this->body = ['error' => '記事が見つかりません', '_template' => 'error/404'];
            return $this;
        }

        $categoryType = (string) ($article['category_type'] ?? '');
        $template     = $categoryType === 'youtube' ? 'articles/youtube-detail' : 'articles/detail';

        $this->body = [
            'article'    => $article,
            'page_title' => (string) $article['title'],
            '_template'  => $template,
        ];

        return $this;
    }
}
