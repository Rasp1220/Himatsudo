<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Himatsudo\Interfaces\ArticleInterface;

final class ArticleService implements ArticleInterface
{
    use SqlFileTrait;

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

        $base  = $this->sql('articles/get_list_base.sql');
        $items = $this->pdo->fetchAll(
            $base . " {$where} ORDER BY a.published_at DESC, a.created_at DESC LIMIT :limit OFFSET :offset",
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

        $base  = $this->sql('articles/get_admin_list_base.sql');
        $items = $this->pdo->fetchAll(
            $base . " {$where} ORDER BY a.created_at DESC LIMIT :limit OFFSET :offset",
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
        $sql = $this->sql('articles/get_by_slug.sql');
        if ($publishedOnly) {
            $sql = str_replace('WHERE a.slug = :slug', "WHERE a.slug = :slug AND a.status = 'published'", $sql);
        }
        $row = $this->pdo->fetchOne($sql, ['slug' => $slug]);
        return $row ? $this->normalizeArticle($row) : null;
    }

    public function getById(int $id): ?array
    {
        $row = $this->pdo->fetchOne($this->sql('articles/get_by_id.sql'), ['id' => $id]);
        return $row ? $this->normalizeArticle($row) : null;
    }

    public function getLatest(int $limit = 10): array
    {
        return $this->pdo->fetchAll($this->sql('articles/get_latest.sql'), ['limit' => $limit]);
    }

    public function getLatestByCategory(int $categoryId, int $limit = 8): array
    {
        return $this->pdo->fetchAll(
            $this->sql('articles/get_latest_by_category.sql'),
            ['category_id' => $categoryId, 'limit' => $limit]
        );
    }

    public function getLatestExcludeType(string $excludeType, int $limit = 20): array
    {
        return $this->pdo->fetchAll(
            $this->sql('articles/get_latest_exclude_type.sql'),
            ['exclude_type' => $excludeType, 'limit' => $limit]
        );
    }

    public function create(array $data): array
    {
        if (!empty($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        }
        $fields = array_keys($data);
        $this->pdo->perform(
            'INSERT INTO articles (' . implode(', ', $fields) . ') VALUES (:' . implode(', :', $fields) . ')',
            $data
        );
        return $this->getById((int) $this->pdo->lastInsertId()) ?? [];
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
        $this->pdo->perform('UPDATE articles SET ' . implode(', ', $sets) . ' WHERE id = :id', $data);
        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->pdo->perform($this->sql('articles/delete.sql'), ['id' => $id])->rowCount();
    }

    public function search(string $keyword, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $like   = '%' . $keyword . '%';
        $where  = "WHERE a.status = 'published' AND (a.title LIKE :keyword OR a.excerpt LIKE :keyword)";
        $base   = $this->sql('articles/search_base.sql');

        $items = $this->pdo->fetchAll(
            $base . " {$where} ORDER BY a.published_at DESC, a.created_at DESC LIMIT :limit OFFSET :offset",
            ['keyword' => $like, 'limit' => $perPage, 'offset' => $offset]
        );
        $total = (int) $this->pdo->fetchValue(
            "SELECT COUNT(*) FROM articles a {$where}",
            ['keyword' => $like]
        );

        return [
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) ceil($total / max(1, $perPage)),
            'keyword'   => $keyword,
        ];
    }

    public function getPrev(string $publishedAt, int $currentId): ?array
    {
        $row = $this->pdo->fetchOne(
            $this->sql('articles/get_prev.sql'),
            ['published_at' => $publishedAt, 'current_id' => $currentId]
        );
        return $row ?: null;
    }

    public function getNext(string $publishedAt, int $currentId): ?array
    {
        $row = $this->pdo->fetchOne(
            $this->sql('articles/get_next.sql'),
            ['published_at' => $publishedAt, 'current_id' => $currentId]
        );
        return $row ?: null;
    }

    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $rows = $this->pdo->fetchAll(
            'SELECT a.id, a.title, a.slug, a.excerpt, a.eye_catch_image, a.youtube_thumbnail, a.published_at,
                    c.name AS category_name, c.slug AS category_slug
             FROM articles a
             LEFT JOIN categories c ON a.category_id = c.id
             WHERE a.id IN (:ids) AND a.status = \'published\'',
            ['ids' => $ids]
        );
        $indexed = array_column($rows, null, 'id');
        return array_values(array_filter(array_map(fn($id) => $indexed[$id] ?? null, $ids)));
    }

    private function normalizeArticle(array $row): array
    {
        if (array_key_exists('related_article_ids', $row)) {
            $row['related_article_ids'] = !empty($row['related_article_ids'])
                ? (json_decode((string) $row['related_article_ids'], true) ?? [])
                : [];
        }
        return $row;
    }
}
