<?php
/**
 * @var array<int, array<string, mixed>> $articles
 * @var array<int, array<string, mixed>> $categories
 * @var array<string, mixed>|null        $current_category
 * @var int      $page
 * @var int      $last_page
 * @var int      $total
 * @var int|null $category_id
 * @var string   $page_title
 * @var string|null $list_base_url   set by Blog / Youtube pages
 * @var string|null $category_slug   set by Category pages
 */
$this->setLayout('layout');
$this->page_title = $page_title ?? '記事一覧';

if (!empty($list_base_url)) {
    $baseUrl = (string) $list_base_url;
} elseif (!empty($category_slug)) {
    $baseUrl = '/' . rawurlencode((string) $category_slug);
} else {
    $baseUrl = '/articles';
}

$pageUrl = static function (int $p, string $base, ?int $catId): string {
    $params = [];
    if ($catId !== null && $base === '/articles') {
        $params[] = 'category_id=' . $catId;
    }
    if ($p > 1) {
        $params[] = 'page=' . $p;
    }
    return $base . ($params ? '?' . implode('&', $params) : '');
};

$categoryUrl = static function (array $cat): string {
    return match ($cat['type'] ?? 'custom') {
        'blog'    => '/blog',
        'youtube' => '/youtube',
        default   => '/' . rawurlencode((string) $cat['slug']),
    };
};

$articleUrl = static function (array $article): string {
    return '/articles/' . rawurlencode((string) $article['slug']);
};
?>

<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        <?php if (!empty($current_category)): ?>
        <li class="breadcrumb-item"><a href="/articles">記事一覧</a></li>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">
            <?= $this->h((string) ($current_category['name'] ?? $page_title)) ?>
        </li>
        <?php else: ?>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">記事一覧</li>
        <?php endif; ?>
    </ol>
</nav>

<h1 class="page-title"><?= $this->h($this->page_title) ?></h1>

<div class="content-layout">
    <div class="content-main">
        <?php if (!empty($articles)): ?>
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
            <?php
                $thumb   = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null;
                $catType = $article['category_type'] ?? 'custom';
            ?>
            <article class="card">
                <a href="<?= $this->h($articleUrl($article)) ?>">
                    <?php if ($thumb): ?>
                    <img src="<?= $this->h($thumb) ?>"
                         alt="<?= $this->h($article['title']) ?>"
                         class="card-img"
                         loading="lazy">
                    <?php else: ?>
                    <div class="card-no-img">NO IMAGE</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <?php if (!empty($article['category_name'])): ?>
                        <span class="badge <?= $this->h($catType) ?>"
                              style="display:inline-block;margin-bottom:.4rem">
                            <?= $this->h($article['category_name']) ?>
                        </span>
                        <?php endif; ?>
                        <h2 class="card-title"><?= $this->h($article['title']) ?></h2>
                        <?php if (!empty($article['excerpt'])): ?>
                        <p style="font-size:.875rem;color:#475569;margin-top:.4rem">
                            <?= $this->h(mb_strimwidth((string) $article['excerpt'], 0, 60, '…')) ?>
                        </p>
                        <?php endif; ?>
                        <?php if (!empty($article['published_at'])): ?>
                        <p class="card-meta" style="margin-top:.4rem">
                            <?= $this->h(date('Y年m月d日', strtotime((string) $article['published_at']))) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if (($last_page ?? 1) > 1): ?>
        <nav class="pagination" aria-label="ページネーション">
            <?php if ($page > 1): ?>
            <a href="<?= $this->h($pageUrl($page - 1, $baseUrl, $category_id ?? null)) ?>">&laquo;</a>
            <?php endif; ?>
            <?php for ($p = max(1, $page - 2); $p <= min($last_page, $page + 2); $p++): ?>
            <?php if ($p === $page): ?>
            <span class="current"><?= $p ?></span>
            <?php else: ?>
            <a href="<?= $this->h($pageUrl($p, $baseUrl, $category_id ?? null)) ?>"><?= $p ?></a>
            <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $last_page): ?>
            <a href="<?= $this->h($pageUrl($page + 1, $baseUrl, $category_id ?? null)) ?>">&raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="no-articles-msg">まだ記事がありません。</div>
        <?php endif; ?>
    </div>

    <?php if (!empty($categories)): ?>
    <aside class="content-aside sidebar">
        <h3>カテゴリ</h3>
        <ul>
            <li>
                <a href="/articles"<?= (empty($category_id) && empty($list_base_url) && empty($category_slug)) ? ' class="active"' : '' ?>>すべて</a>
            </li>
            <?php foreach ($categories as $cat): ?>
            <?php
                $catUrl   = $categoryUrl($cat);
                $isActive = (!empty($category_id) && (int) ($cat['id'] ?? 0) === (int) $category_id)
                         || (!empty($list_base_url) && $catUrl === (string) $list_base_url)
                         || (!empty($category_slug) && ($cat['slug'] ?? '') === (string) $category_slug);
            ?>
            <li>
                <a href="<?= $this->h($catUrl) ?>"<?= $isActive ? ' class="active"' : '' ?>>
                    <?= $this->h((string) $cat['name']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </aside>
    <?php endif; ?>
</div>
