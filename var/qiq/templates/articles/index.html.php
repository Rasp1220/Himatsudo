<?php
/**
 * @var array<int, array<string, mixed>> $articles
 * @var array<int, array<string, mixed>> $categories
 * @var array<string, mixed>|null        $current_category
 * @var int    $page
 * @var int    $last_page
 * @var int    $total
 * @var int|null $category_id
 * @var string $page_title
 * @var string|null $list_base_url   set by Blog / Youtube pages
 * @var string|null $category_slug   set by Category pages
 */
$this->setLayout('layout');
$this->page_title = $page_title ?? '記事一覧';

// Determine base URL for pagination and filters
if (!empty($list_base_url)) {
    $baseUrl     = (string) $list_base_url;
    $showFilters = false;
} elseif (!empty($category_slug)) {
    $baseUrl     = '/' . rawurlencode((string) $category_slug);
    $showFilters = false;
} else {
    $baseUrl     = '/articles';
    $showFilters = true;
}

$pageUrl = static function (int $p, string $base, ?int $catId) use ($showFilters): string {
    $params = [];
    if ($showFilters && $catId !== null) {
        $params[] = 'category_id=' . $catId;
    }
    if ($p > 1) {
        $params[] = 'page=' . $p;
    }
    return $base . ($params ? '?' . implode('&', $params) : '');
};

$categoryFilterUrl = static function (array $cat, string $base): string {
    return match ($cat['type'] ?? 'custom') {
        'blog'    => '/blog',
        'youtube' => '/youtube',
        default   => $base . '?category_id=' . (int) $cat['id'],
    };
};

$articleUrl = static function (array $article): string {
    return '/' . rawurlencode((string) $article['slug']);
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

<?php if ($showFilters && !empty($categories)): ?>
<div class="article-list-filters">
    <a href="<?= $this->h($baseUrl) ?>"
       class="category-link<?= $category_id === null ? ' category-link--active' : '' ?>">
        すべて
    </a>
    <?php foreach ($categories as $cat): ?>
    <a href="<?= $this->h($categoryFilterUrl($cat, $baseUrl)) ?>"
       class="category-link<?= (int) ($cat['id'] ?? 0) === $category_id ? ' category-link--active' : '' ?>">
        <?= $this->h((string) $cat['name']) ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($articles)): ?>
<div class="article-card-grid">
    <?php foreach ($articles as $article): ?>
    <?php
        $thumb   = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null;
        $catType = $article['category_type'] ?? 'custom';
    ?>
    <article class="article-card">
        <a href="<?= $this->h($articleUrl($article)) ?>" class="article-card__link">
            <?php if ($thumb): ?>
            <div class="article-card__thumb">
                <img src="<?= $this->h($thumb) ?>"
                     alt="<?= $this->h($article['title']) ?>"
                     class="article-card__img"
                     loading="lazy">
                <?php if ($catType === 'youtube'): ?>
                <span class="article-card__play-icon">▶</span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <div class="article-card__body">
                <?php if (!empty($article['category_name'])): ?>
                <span class="badge <?= $this->h($catType) ?>">
                    <?= $this->h($article['category_name']) ?>
                </span>
                <?php endif; ?>
                <h2 class="article-card__title"><?= $this->h($article['title']) ?></h2>
                <?php if (!empty($article['excerpt'])): ?>
                <p class="article-card__excerpt">
                    <?= $this->h(mb_strimwidth((string) $article['excerpt'], 0, 80, '…')) ?>
                </p>
                <?php endif; ?>
                <?php if (!empty($article['published_at'])): ?>
                <time class="article-card__date" datetime="<?= $this->h($article['published_at']) ?>">
                    <?= $this->h(date('Y年m月d日', strtotime((string) $article['published_at']))) ?>
                </time>
                <?php endif; ?>
            </div>
        </a>
    </article>
    <?php endforeach; ?>
</div>

<?php if ($last_page > 1): ?>
<nav class="pagination" aria-label="ページネーション">
    <?php if ($page > 1): ?>
    <a href="<?= $this->h($pageUrl($page - 1, $baseUrl, $category_id ?? null)) ?>">&laquo;</a>
    <?php endif; ?>

    <?php
    $rangeStart = max(1, $page - 2);
    $rangeEnd   = min($last_page, $page + 2);
    if ($rangeStart > 1): ?>
    <a href="<?= $this->h($pageUrl(1, $baseUrl, $category_id ?? null)) ?>">1</a>
    <?php if ($rangeStart > 2): ?><span>…</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($p = $rangeStart; $p <= $rangeEnd; $p++): ?>
    <?php if ($p === $page): ?>
    <span class="current"><?= $p ?></span>
    <?php else: ?>
    <a href="<?= $this->h($pageUrl($p, $baseUrl, $category_id ?? null)) ?>"><?= $p ?></a>
    <?php endif; ?>
    <?php endfor; ?>

    <?php if ($rangeEnd < $last_page): ?>
    <?php if ($rangeEnd < $last_page - 1): ?><span>…</span><?php endif; ?>
    <a href="<?= $this->h($pageUrl($last_page, $baseUrl, $category_id ?? null)) ?>"><?= $last_page ?></a>
    <?php endif; ?>

    <?php if ($page < $last_page): ?>
    <a href="<?= $this->h($pageUrl($page + 1, $baseUrl, $category_id ?? null)) ?>">&raquo;</a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php else: ?>
<div class="no-articles-msg">まだ記事がありません。</div>
<?php endif; ?>
