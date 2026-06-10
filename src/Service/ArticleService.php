<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Himatsudo\Interfaces\ArticleInterface;

final class ArticleService implements ArticleInterface
{
    use SqlFileTrait;

    /** INSERT/UPDATE で許可するカラム。識別子をクエリに連結するため必ずここで制限する。 */
    private const WRITABLE_FIELDS = [
        'title', 'slug', 'status', 'content', 'blocks', 'excerpt', 'eye_catch_image',
        'category_id', 'author_id', 'youtube_url', 'youtube_video_id', 'youtube_thumbnail',
        'published_at',
    ];

    public function __construct(private readonly ExtendedPdoInterface $pdo) {}

    public function getList(int $page = 1, int $perPage = 15, ?int $categoryId = null, string $status = 'published'): array
    {
        $where = 'WHERE a.status = :status';
        $bind  = ['status' => $status];

        if ($categoryId !== null) {
            $where .= ' AND a.category_id = :category_id';
            $bind['category_id'] = $categoryId;
        }

        return $this->fetchPage(
            'articles/get_list_base.sql',
            $where,
            'ORDER BY a.published_at DESC, a.created_at DESC',
            $bind,
            $page,
            $perPage
        );
    }

    public function getAdminList(int $page = 1, int $perPage = 20, ?int $categoryId = null, ?string $status = null, ?string $keyword = null): array
    {
        $where = 'WHERE 1=1';
        $bind  = [];

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

        return $this->fetchPage(
            'articles/get_admin_list_base.sql',
            $where,
            'ORDER BY a.created_at DESC',
            $bind,
            $page,
            $perPage
        );
    }

    public function getBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = $this->sql('articles/get_by_slug.sql');
        if ($publishedOnly) {
            $sql = str_replace('WHERE a.slug = :slug', "WHERE a.slug = :slug AND a.status = 'published'", $sql);
        }
        $row = $this->pdo->fetchOne($sql, ['slug' => $slug]);
        return $row ?: null;
    }

    public function getById(int $id): ?array
    {
        $row = $this->pdo->fetchOne($this->sql('articles/get_by_id.sql'), ['id' => $id]);
        return $row ?: null;
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

    public function create(array $data): array
    {
        $data = $this->filterWritable($data);
        if (($data['status'] ?? '') === 'published' && empty($data['published_at'])) {
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
        $data = $this->filterWritable($data);
        if (($data['status'] ?? '') === 'published' && !array_key_exists('published_at', $data)) {
            $current = $this->getById($id);
            if ($current && empty($current['published_at'])) {
                $data['published_at'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
            }
        }
        if ($data !== []) {
            $sets       = array_map(fn($f) => "{$f} = :{$f}", array_keys($data));
            $data['id'] = $id;
            $this->pdo->perform('UPDATE articles SET ' . implode(', ', $sets) . ' WHERE id = :id', $data);
        }
        return $this->getById($id);
    }

    public function delete(int $id): bool
    {
        return (bool) $this->pdo->perform($this->sql('articles/delete.sql'), ['id' => $id])->rowCount();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function filterWritable(array $data): array
    {
        return array_intersect_key($data, array_flip(self::WRITABLE_FIELDS));
    }

    /**
     * ベースSQLに WHERE/ORDER BY/LIMIT を付加して1ページ分と総件数を取得する。
     *
     * @param array<string, mixed> $bind
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int, last_page: int}
     */
    private function fetchPage(string $baseSqlFile, string $where, string $orderBy, array $bind, int $page, int $perPage): array
    {
        $items = $this->pdo->fetchAll(
            $this->sql($baseSqlFile) . " {$where} {$orderBy} LIMIT :limit OFFSET :offset",
            $bind + ['limit' => $perPage, 'offset' => ($page - 1) * $perPage]
        );
        $total = (int) $this->pdo->fetchValue("SELECT COUNT(*) FROM articles a {$where}", $bind);

        return [
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) ceil($total / max(1, $perPage)),
        ];
    }
}
