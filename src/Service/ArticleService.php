<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Himatsudo\Interfaces\ArticleInterface;

final class ArticleService implements ArticleInterface
{
    public function __construct(private readonly ExtendedPdoInterface $pdo) {}

    public function getList(int $page = 1, int $perPage = 15, ?int $categoryId = null, string $status = 'published'): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = 'WHERE a.status = :status';
        $bind   = ['status' => $status, 'limit' => $perPage, 'offset' => $offset];

        if ($categoryId !== null) {
            $where .= ' AND a.category_id = :category_id';
            $bind['category_id'] = $categoryId;
        }

        $items = $this->pdo->fetchAll(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.eye_catch_image, a.status,
                    a.youtube_thumbnail, a.published_at, a.created_at, a.updated_at,
                    c.id AS category_id, c.name AS category_name, c.slug AS category_slug, c.type AS category_type,
                    u.id AS author_id, u.name AS author_name
             FROM articles a
             LEFT JOIN categories c ON c.id = a.category_id
             LEFT JOIN users u      ON u.id = a.author_id
             {$where}
             ORDER BY a.published_at DESC, a.created_at DESC
             LIMIT :limit OFFSET :offset",
            $bind
        );

        $countBind  = ['status' => $status];
        $countWhere = 'WHERE status = :status';
        if ($categoryId !== null) {
            $countWhere .= ' AND category_id = :category_id';
            $countBind['category_id'] = $categoryId;
        }
        $total = (int) $this->pdo->fetchValue("SELECT COUNT(*) FROM articles {$countWhere}", $countBind);

        return [
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) ceil($total / max(1, $perPage)),
        ];
    }

    public function getAdminList(int $page = 1, int $perPage = 20, ?int $categoryId = null, ?string $status = null, ?string $keyword = null): array
    {
        $where = 'WHERE 1=1';
        $bind  = ['limit' => $perPage, 'offset' => ($page - 1) * $perPage];

        if ($status !== null) {
            $where .= ' AND a.status = :status';
            $bind['status'] = $status;
        }
        if ($categoryId !== null) {
            $where .= ' AND a.category_id = :category_id';
            $bind['category_id'] = $categoryId;
        }
        if ($keyword !== null) {
            $where .= ' AND (a.title LIKE :keyword OR a.excerpt LIKE :keyword)';
            $bind['keyword'] = '%' . $keyword . '%';
        }

        $items = $this->pdo->fetchAll(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.eye_catch_image, a.status,
                    a.youtube_thumbnail, a.published_at, a.created_at, a.updated_at,
                    c.id AS category_id, c.name AS category_name, c.type AS category_type,
                    u.id AS author_id, u.name AS author_name
             FROM articles a
             LEFT JOIN categories c ON c.id = a.category_id
             LEFT JOIN users u      ON u.id = a.author_id
             {$where}
             ORDER BY a.created_at DESC
             LIMIT :limit OFFSET :offset",
            $bind
        );

        $countBind = array_filter($bind, fn($k) => !in_array($k, ['limit', 'offset']), ARRAY_FILTER_USE_KEY);
        $total     = (int) $this->pdo->fetchValue("SELECT COUNT(*) FROM articles a {$where}", $countBind);

        return [
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) ceil($total / max(1, $perPage)),
        ];
    }

    public function getBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $where = 'WHERE a.slug = :slug' . ($publishedOnly ? " AND a.status = 'published'" : '');
        $row   = $this->pdo->fetchOne(
            "SELECT a.*, c.name AS category_name, c.slug AS category_slug, c.type AS category_type,
                    u.name AS author_name
             FROM articles a
             LEFT JOIN categories c ON c.id = a.category_id
             LEFT JOIN users u      ON u.id = a.author_id
             {$where} LIMIT 1",
            ['slug' => $slug]
        );
        return $row ?: null;
    }

    public function getById(int $id): ?array
    {
        $row = $this->pdo->fetchOne(
            "SELECT a.*, c.name AS category_name, c.slug AS category_slug, c.type AS category_type,
                    u.name AS author_name
             FROM articles a
             LEFT JOIN categories c ON c.id = a.category_id
             LEFT JOIN users u      ON u.id = a.author_id
             WHERE a.id = :id LIMIT 1",
            ['id' => $id]
        );
        return $row ?: null;
    }

    public function getLatest(int $limit = 10): array
    {
        return $this->pdo->fetchAll(
            "SELECT a.id, a.title, a.slug, a.excerpt, a.eye_catch_image, a.youtube_thumbnail,
                    a.published_at, c.name AS category_name, c.slug AS category_slug, c.type AS category_type
             FROM articles a
             LEFT JOIN categories c ON c.id = a.category_id
             WHERE a.status = 'published'
             ORDER BY a.published_at DESC, a.created_at DESC
             LIMIT :limit",
            ['limit' => $limit]
        );
    }

    public function getLatestByCategory(int $categoryId, int $limit = 8): array
    {
        return $this->pdo->fetchAll(
            "SELECT a.id, a.title, a.slug, a.eye_catch_image, a.youtube_thumbnail,
                    a.published_at, a.created_at
             FROM articles a
             WHERE a.status = 'published' AND a.category_id = :category_id
             ORDER BY a.published_at DESC, a.created_at DESC
             LIMIT :limit",
            ['category_id' => $categoryId, 'limit' => $limit]
        );
    }

    public function create(array $data): array
    {
        if (!empty($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        }
        $fields = array_keys($data);
        $sql    = 'INSERT INTO articles (' . implode(', ', $fields) . ') VALUES (:' . implode(', :', $fields) . ')';
        $this->pdo->perform($sql, $data);
        $id = (int) $this->pdo->lastInsertId();
        return $this->getById($id) ?? [];
    }

    public function update(int $id, array $data): ?array
    {
        if (!empty($data['status']) && $data['status'] === 'published' && !array_key_exists('published_at', $data)) {
            $current = $this->getById($id);
            if ($current && empty($current['published_at'])) {
                $data['published_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            }
        }
        $sets      = array_map(fn($f) => "{$f} = :{$f}", array_keys($data));
        $data['id'] = $id;
        $sql        = 'UPDATE articles SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $this->pdo->perform($sql, $data);
        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->pdo->perform('DELETE FROM articles WHERE id = :id', ['id' => $id])->rowCount();
    }
}
