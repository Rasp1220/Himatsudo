<?php
/**
 * @var array<int, array<string, mixed>> $latest_articles
 * @var array<int, array{category: array<string, mixed>, articles: array<int, array<string, mixed>>}> $categories_with_articles
 * @var string $page_title
 */
$this->setLayout('layout');
$this->page_title = $page_title ?? 'ホーム';

$categoryUrl = static function (array $category): string {
    return match ($category['type'] ?? 'custom') {
        'blog'    => '/blog',
        'youtube' => '/youtube',
        default   => '/' . rawurlencode((string) $category['slug']),
    };
};

$articleUrl = static function (array $article): string {
    $prefix = ($article['category_type'] ?? 'custom') === 'blog' ? '/blog' : '/articles';
    return $prefix . '/' . rawurlencode((string) $article['slug']);
};

$sections = [];
if (!empty($latest_articles)) {
    $sections[] = [
        'title'       => '新着記事',
        'badge_class' => '',
        'url'         => '/articles',
        'articles'    => $latest_articles,
    ];
}
foreach ($categories_with_articles as $group) {
    $cat      = $group['category'];
    $articles = $group['articles'];
    if (!empty($articles)) {
        $sections[] = [
            'title'       => (string) $cat['name'],
            'badge_class' => (string) ($cat['type'] ?? ''),
            'url'         => $categoryUrl($cat),
            'articles'    => $articles,
        ];
    }
}
$swiperCount = count($sections);
?>

<?php foreach ($sections as $idx => $section): ?>
<section class="cat-section">
    <div class="cat-header">
        <h2 class="cat-title">
            <?php if ($section['badge_class']): ?>
            <span class="badge <?= $this->h($section['badge_class']) ?>"><?= $this->h($section['title']) ?></span>
            <?php else: ?>
            <?= $this->h($section['title']) ?>
            <?php endif; ?>
        </h2>
        <a href="<?= $this->h($section['url']) ?>" class="cat-more">もっと見る &rarr;</a>
    </div>
    <div class="cat-swiper-outer">
        <button class="cat-swiper-btn" id="prev-<?= $idx ?>">&#8249;</button>
        <div class="swiper cat-swiper" id="swiper-<?= $idx ?>">
            <div class="swiper-wrapper">
                <?php foreach ($section['articles'] as $article): ?>
                <?php $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null; ?>
                <div class="swiper-slide">
                    <a href="<?= $this->h($articleUrl($article)) ?>" class="carousel-card-link">
                        <?php if ($thumb): ?>
                        <div class="carousel-thumb">
                            <img src="<?= $this->h($thumb) ?>"
                                 alt="<?= $this->h($article['title']) ?>"
                                 loading="lazy">
                        </div>
                        <?php else: ?>
                        <div class="carousel-no-thumb"><span>NO IMAGE</span></div>
                        <?php endif; ?>
                        <div class="carousel-info">
                            <p class="carousel-title"><?= $this->h($article['title']) ?></p>
                            <?php if (!empty($article['published_at'])): ?>
                            <time class="carousel-date" datetime="<?= $this->h($article['published_at']) ?>">
                                <?= $this->h(date('Y年m月d日', strtotime((string) $article['published_at']))) ?>
                            </time>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="cat-swiper-btn" id="next-<?= $idx ?>">&#8250;</button>
    </div>
</section>
<?php endforeach; ?>

<?php if (empty($sections)): ?>
<div class="no-articles-msg">まだ記事がありません。</div>
<?php endif; ?>

<script>
(function () {
    var configs = [];
    <?php for ($i = 0; $i < $swiperCount; $i++): ?>
    configs.push({ el: '#swiper-<?= $i ?>', prev: '#prev-<?= $i ?>', next: '#next-<?= $i ?>' });
    <?php endfor; ?>
    configs.forEach(function (c) {
        new Swiper(c.el, {
            slidesPerView: 1.4,
            spaceBetween: 12,
            navigation: { prevEl: c.prev, nextEl: c.next },
            breakpoints: {
                480:  { slidesPerView: 2.3 },
                768:  { slidesPerView: 3   },
                1024: { slidesPerView: 4   }
            }
        });
    });
}());
</script>
