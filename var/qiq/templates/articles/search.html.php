<?php
/**
 * @var string $q
 * @var array<int, array<string, mixed>> $items
 * @var int $total
 * @var int $page
 * @var int $last_page
 */
$this->setLayout('layout');
$this->page_title = 'サイト内検索';

$articleUrl = static function (array $article): string {
    $prefix = ($article['category_type'] ?? 'custom') === 'blog' ? '/blog' : '/articles';
    return $prefix . '/' . rawurlencode((string) $article['slug']);
};

$pageUrl = static function (int $p, string $q): string {
    return '/search?q=' . rawurlencode($q) . ($p > 1 ? '&page=' . $p : '');
};
?>

<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">サイト内検索</li>
    </ol>
</nav>

<h1 class="page-title">サイト内検索</h1>

<div class="search-form-wrap">
    <form class="search-form-block" action="/search" method="get" role="search">
        <input type="search" name="q"
               value="<?= $this->h($q) ?>"
               placeholder="キーワードを入力…"
               aria-label="検索キーワード"
               class="search-input-block">
        <button type="submit" class="search-btn-block">検索</button>
    </form>
</div>

<?php if ($q !== ''): ?>
<p class="search-summary">
    「<strong><?= $this->h($q) ?></strong>」の検索結果：<?= $this->h((string) $total) ?>件
</p>
<?php endif; ?>

<?php if (!empty($items)): ?>
<div class="search-results-list">
    <?php foreach ($items as $article): ?>
    <?php
        $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null;
    ?>
    <a href="<?= $this->h($articleUrl($article)) ?>" class="search-card">
        <div class="search-card-thumb">
            <?php if ($thumb): ?>
            <img src="<?= $this->h($thumb) ?>"
                 alt="<?= $this->h($article['title']) ?>"
                 loading="lazy">
            <?php else: ?>
            <span class="search-card-no-img">NO IMAGE</span>
            <?php endif; ?>
        </div>
        <div class="search-card-body">
            <?php if (!empty($article['category_name'])): ?>
            <span class="badge <?= $this->h($article['category_type'] ?? '') ?>" style="display:inline-block;margin-bottom:.35rem;font-size:.7rem">
                <?= $this->h($article['category_name']) ?>
            </span>
            <?php endif; ?>
            <div class="search-card-title"><?= $this->h($article['title']) ?></div>
            <?php if (!empty($article['published_at'])): ?>
            <div class="search-card-date">
                <?= $this->h(date('Y年m月d日', strtotime((string) $article['published_at']))) ?>
            </div>
            <?php endif; ?>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php if ($last_page > 1): ?>
<nav class="pagination" aria-label="ページネーション" style="margin-top:2rem">
    <?php if ($page > 1): ?>
    <a href="<?= $this->h($pageUrl($page - 1, $q)) ?>">&laquo;</a>
    <?php endif; ?>
    <?php for ($p = max(1, $page - 2); $p <= min($last_page, $page + 2); $p++): ?>
    <?php if ($p === $page): ?>
    <span class="current"><?= $p ?></span>
    <?php else: ?>
    <a href="<?= $this->h($pageUrl($p, $q)) ?>"><?= $p ?></a>
    <?php endif; ?>
    <?php endfor; ?>
    <?php if ($page < $last_page): ?>
    <a href="<?= $this->h($pageUrl($page + 1, $q)) ?>">&raquo;</a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<?php elseif ($q !== ''): ?>
<p class="no-articles-msg">該当する記事が見つかりませんでした。</p>
<?php endif; ?>
