<?php
/** @var array<int, array<string, mixed>> $articles */
/** @var array<int, array<string, mixed>> $categories */
/** @var int $total */
/** @var int $page */
/** @var int $last_page */
/** @var int|null $category_id */
/** @var array<string, mixed>|null $current_category */
$this->setLayout('layout');
$this->page_title = $page_title ?? '記事一覧';
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
                <?php elseif (($article['category_type'] ?? '') === 'youtube'): ?>
                <div style="height:180px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:2rem">▶</div>
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
                    <?php if (!empty($article['excerpt'])): ?>
                    <p style="font-size:.875rem;color:#475569;margin-top:.4rem">
                        <?= $this->h(mb_strimwidth((string) $article['excerpt'], 0, 80, '…')) ?>
                    </p>
                    <?php endif; ?>
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
            <a href="?page=<?= $page - 1 ?><?= $category_id ? '&category_id=' . $category_id : '' ?>">&laquo;</a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min($last_page, $page + 2); $i++): ?>
            <?php if ($i === $page): ?>
            <span class="current"><?= $i ?></span>
            <?php else: ?>
            <a href="?page=<?= $i ?><?= $category_id ? '&category_id=' . $category_id : '' ?>"><?= $i ?></a>
            <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $last_page): ?>
            <a href="?page=<?= $page + 1 ?><?= $category_id ? '&category_id=' . $category_id : '' ?>">&raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <aside style="width:200px;flex-shrink:0">
        <h3 style="font-weight:700;margin-bottom:1rem">カテゴリ</h3>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:.5rem">
            <li><a href="/articles" style="color:<?= $category_id === null ? '#2563eb' : '#334155' ?>;font-weight:<?= $category_id === null ? '700' : '400' ?>">すべて</a></li>
            <?php foreach ($categories as $cat): ?>
            <li>
                <a href="/articles?category_id=<?= (int) $cat['id'] ?>"
                   style="color:<?= (int) ($category_id ?? 0) === (int) $cat['id'] ? '#2563eb' : '#334155' ?>;font-weight:<?= (int) ($category_id ?? 0) === (int) $cat['id'] ? '700' : '400' ?>">
                    <?= $this->h($cat['name']) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </aside>
</div>
