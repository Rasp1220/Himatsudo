<?php
/**
 * @var array<int, array<string, mixed>> $latest_articles
 * @var array<int, array{category: array<string, mixed>, articles: array<int, array<string, mixed>>}> $categories_with_articles
 * @var array<int, array<string, mixed>> $categories
 * @var string $page_title
 */
$this->setLayout('layout');
$this->page_title = $page_title ?? 'ホーム';

/**
 * カテゴリタイプに応じたURL生成
 */
$categoryUrl = static function (array $category): string {
    return match($category['type'] ?? 'custom') {
        'blog'    => '/blog',
        'youtube' => '/youtube',
        default   => '/articles?category_id=' . (int) $category['id'],
    };
};

/**
 * 記事URLを slug から生成
 */
$articleUrl = static function (array $article): string {
    return '/' . rawurlencode((string) $article['slug']);
};
?>

<section class="top-hero">
    <h1 class="top-hero__title">ひまつど</h1>
    <p class="top-hero__lead">日常のひまつぶしに、ちょっとだけ役立つ情報をお届けします。</p>
</section>

<?php if (!empty($latest_articles)): ?>
<section class="top-section">
    <div class="top-section__header">
        <h2 class="top-section__title">新着記事</h2>
        <a href="/articles" class="top-section__more">もっと見る &rarr;</a>
    </div>
    <div class="article-card-grid">
        <?php foreach (array_slice($latest_articles, 0, 6) as $article): ?>
        <?php
            $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null;
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
                    <h3 class="article-card__title"><?= $this->h($article['title']) ?></h3>
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
</section>
<?php endif; ?>

<?php foreach ($categories_with_articles as $group): ?>
<?php
    $cat      = $group['category'];
    $articles = $group['articles'];
    if (empty($articles)) { continue; }
?>
<section class="top-section">
    <div class="top-section__header">
        <h2 class="top-section__title">
            <a href="<?= $this->h($categoryUrl($cat)) ?>" class="top-section__cat-link">
                <?= $this->h($cat['name']) ?>
            </a>
        </h2>
        <a href="<?= $this->h($categoryUrl($cat)) ?>" class="top-section__more">もっと見る &rarr;</a>
    </div>
    <div class="article-card-grid">
        <?php foreach (array_slice($articles, 0, 4) as $article): ?>
        <?php
            $thumb   = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null;
            $catType = $cat['type'] ?? 'custom';
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
                    <h3 class="article-card__title"><?= $this->h($article['title']) ?></h3>
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
</section>
<?php endforeach; ?>

<?php if (empty($latest_articles) && empty($categories_with_articles)): ?>
<section class="top-empty">
    <p>まだ記事がありません。</p>
</section>
<?php endif; ?>
