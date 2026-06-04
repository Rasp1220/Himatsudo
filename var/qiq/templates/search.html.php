<?php
/**
 * @var array<int, array<string, mixed>> $items
 * @var int    $total
 * @var int    $page
 * @var int    $last_page
 * @var string $keyword
 */
$this->setLayout('layout');
$this->page_title = $keyword !== '' ? "「{$keyword}」の検索結果" : 'サイト内検索';
?>

<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">検索</li>
    </ol>
</nav>

<div class="search-page">
    <h1 class="search-heading">サイト内検索</h1>

    <form class="search-form" action="/search" method="get" role="search">
        <input
            type="search"
            name="q"
            value="<?= $this->h($keyword) ?>"
            placeholder="キーワードを入力"
            class="search-input"
            autofocus
        >
        <button type="submit" class="search-submit">検索</button>
    </form>

    <?php if ($keyword !== ''): ?>
    <p class="search-result-count">
        <?php if ($total > 0): ?>
            「<?= $this->h($keyword) ?>」の検索結果: <?= $this->h((string) $total) ?> 件
        <?php else: ?>
            「<?= $this->h($keyword) ?>」に一致する記事は見つかりませんでした。
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <?php if (!empty($items)): ?>
    <ul class="search-results">
        <?php foreach ($items as $item):
            $thumb   = $item['eye_catch_image'] ?? $item['youtube_thumbnail'] ?? '';
            $catType = $item['category_type'] ?? 'custom';
            $url     = '/articles/' . rawurlencode((string) ($item['slug'] ?? ''));
        ?>
        <li class="search-result-item">
            <a href="<?= $this->h($url) ?>" class="search-result-link">
                <div class="search-result-thumbnail">
                    <?php if ($thumb): ?>
                    <img src="<?= $this->h($thumb) ?>"
                         alt="<?= $this->h($item['title'] ?? '') ?>"
                         class="search-result-img"
                         loading="lazy">
                    <?php else: ?>
                    <div class="search-result-no-img">NO IMAGE</div>
                    <?php endif; ?>
                </div>
                <div class="search-result-body">
                    <p class="search-result-title"><?= $this->h($item['title'] ?? '') ?></p>
                    <?php if (!empty($item['published_at'])): ?>
                    <time class="search-result-date" datetime="<?= $this->h($item['published_at']) ?>">
                        <?= $this->h(date('Y年m月d日', strtotime((string) $item['published_at']))) ?>
                    </time>
                    <?php endif; ?>
                    <?php if (!empty($item['category_name'])): ?>
                    <span class="search-result-category badge <?= $this->h($catType) ?>">
                        <?= $this->h($item['category_name']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <?php if (($last_page ?? 1) > 1): ?>
    <nav class="pagination" aria-label="検索結果のページネーション">
        <?php if ($page > 1): ?>
        <a href="/search?q=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>">&laquo;</a>
        <?php endif; ?>
        <?php for ($p = max(1, $page - 2); $p <= min($last_page, $page + 2); $p++): ?>
        <?php if ($p === $page): ?>
        <span class="current"><?= $p ?></span>
        <?php else: ?>
        <a href="/search?q=<?= urlencode($keyword) ?>&page=<?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $last_page): ?>
        <a href="/search?q=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>">&raquo;</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <?php endif; ?>
</div>
