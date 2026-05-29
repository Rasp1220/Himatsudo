<?php
/** @var array<int, array<string, mixed>> $latest_articles */
/** @var array<int, array<string, mixed>> $categories */
$this->setLayout('layout');
$this->page_title = 'ホーム';
?>
<?php $this->sliderArticles = $latest_articles ?? []; ?>
<?= $this->render('components/slider', ['sliderArticles' => $latest_articles ?? []]) ?>

<section>
    <h2 class="page-title">新着記事</h2>
    <?php if (empty($latest_articles)): ?>
    <p style="color:#64748b">まだ記事がありません。</p>
    <?php else: ?>
    <div class="articles-grid">
        <?php foreach ($latest_articles as $article): ?>
        <article class="card">
            <?php $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null; ?>
            <?php if ($thumb): ?>
            <img src="<?= $this->h($thumb) ?>" alt="<?= $this->h($article['title']) ?>" class="card-img">
            <?php endif; ?>
            <div class="card-body">
                <?php if (!empty($article['category_name'])): ?>
                <span class="badge <?= $this->h($article['category_type'] ?? '') ?>">
                    <?= $this->h($article['category_name']) ?>
                </span>
                <?php endif; ?>
                <h3 class="card-title" style="margin-top:.5rem">
                    <a href="/articles/<?= $this->h($article['slug']) ?>">
                        <?= $this->h($article['title']) ?>
                    </a>
                </h3>
                <?php if (!empty($article['excerpt'])): ?>
                <p style="font-size:.875rem;color:#475569;margin-top:.5rem">
                    <?= $this->h(mb_strimwidth((string) $article['excerpt'], 0, 80, '…')) ?>
                </p>
                <?php endif; ?>
                <p class="card-meta" style="margin-top:.5rem">
                    <?= $this->h((string) ($article['published_at'] ? date('Y年m月d日', strtotime((string) $article['published_at'])) : '')) ?>
                </p>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:2rem">
        <a href="/articles" style="padding:.6rem 2rem;background:#2563eb;color:#fff;border-radius:.375rem;font-weight:600">
            記事をもっと見る
        </a>
    </div>
    <?php endif; ?>
</section>

<?php if (!empty($categories)): ?>
<section style="margin-top:3rem">
    <h2 class="page-title">カテゴリ</h2>
    <div style="display:flex;flex-wrap:wrap;gap:1rem">
        <?php foreach ($categories as $cat): ?>
        <a href="/articles?category_id=<?= (int) $cat['id'] ?>"
           style="padding:.5rem 1.25rem;background:#fff;border:1px solid #e2e8f0;border-radius:2rem;font-weight:600;color:#334155">
            <?= $this->h($cat['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
