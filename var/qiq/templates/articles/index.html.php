<?php
/** @var array<int, array<string, mixed>> $articles */
/** @var array<int, array<string, mixed>> $categories */
/** @var int $total */
/** @var int $page */
/** @var int $last_page */
/** @var int|null $category_id */
/** @var array<string, mixed>|null $current_category */
/** @var string|null $list_base_url */
$this->setLayout('layout');
$this->page_title = $page_title ?? '記事一覧';

// Helper: build URL for a category
function categoryUrl(array $cat): string {
    return match($cat['type'] ?? '') {
        'blog'    => '/blog',
        'youtube' => '/youtube',
        default   => '/articles?category_id=' . (int) $cat['id'],
    };
}

// Helper: build pagination link
function pageUrl(?string $listBase, ?int $catId, int $p): string {
    if ($listBase !== null) {
        return $listBase . '?page=' . $p;
    }
    return '?page=' . $p . ($catId !== null ? '&category_id=' . $catId : '');
}
?>
<h1 class="page-title"><?= $this->h($this->page_title) ?></h1>

<div style="display:flex;gap:2rem;align-items:flex-start">
    <div style="flex:1;min-width:0">
        <?php if (empty($articles)): ?>
        <p style="color:#64748b">記事がありません。</p>
        <?php else: ?>
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
            <article class="card">
                <?php $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null; ?>
                <?php if ($thumb): ?>
                <img src="<?= $this->h($thumb) ?>" alt="<?= $this->h($article['title']) ?>" class="card-img">
                <?php else: ?>
                <div class="card-no-img">NO IMAGE</div>
                <?php endif; ?>
                <div class="card-body">
                    <?php if (!empty($article['category_name'])): ?>
                    <span class="badge <?= $this->h($article['category_type'] ?? '') ?>">
                        <?= $this->h($article['category_name']) ?>
                    </span>
                    <?php endif; ?>
                    <h2 class="card-title" style="margin-top:.4rem">
                        <a href="/articles/<?= $this->h($article['slug']) ?>">
                            <?= $this->h($article['title']) ?>
                        </a>
                    </h2>
                    <p class="card-meta" style="margin-top:.4rem">
                        <?= $this->h((string) ($article['published_at'] ? date('Y年m月d日', strtotime((string) $article['published_at'])) : '')) ?>
                    </p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($last_page > 1): ?>
        <nav class="pagination">
            <?php if ($page > 1): ?>
            <a href="<?= $this->h(pageUrl($list_base_url ?? null, $category_id ?? null, $page - 1)) ?>">&laquo;</a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($last_page, $page + 2); $i++): ?>
            <?php if ($i === $page): ?>
            <span class="current"><?= $i ?></span>
            <?php else: ?>
            <a href="<?= $this->h(pageUrl($list_base_url ?? null, $category_id ?? null, $i)) ?>"><?= $i ?></a>
            <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $last_page): ?>
            <a href="<?= $this->h(pageUrl($list_base_url ?? null, $category_id ?? null, $page + 1)) ?>">&raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <aside style="width:200px;flex-shrink:0">
        <h3 style="font-weight:700;margin-bottom:1rem">カテゴリ</h3>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:.5rem">
            <li><a href="/articles" style="color:<?= $category_id === null && ($list_base_url ?? null) === null ? '#2563eb' : '#334155' ?>;font-weight:<?= $category_id === null && ($list_base_url ?? null) === null ? '700' : '400' ?>">すべて</a></li>
            <?php foreach ($categories as $cat): ?>
            <?php $catHref = categoryUrl($cat); $isActive = (int) ($category_id ?? 0) === (int) $cat['id']; ?>
            <li>
                <a href="<?= $this->h($catHref) ?>"
                   style="color:<?= $isActive ? '#2563eb' : '#334155' ?>;font-weight:<?= $isActive ? '700' : '400' ?>">
                    <?= $this->h($cat['name']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </aside>
</div>
