<?php
/**
 * @var array<int,array<string,mixed>>                                                   $latest_articles
 * @var array<int,array{category:array<string,mixed>,articles:array<int,array<string,mixed>>}> $categories_with_articles
 * @var array<int,array<string,mixed>>                                                   $categories
 */
$this->setLayout('layout');
$this->page_title = 'ホーム';

// カテゴリID → 記事配列 のマップを作る
$articlesByCategory = [];
foreach ($categories_with_articles as $group) {
    $articlesByCategory[(int) $group['category']['id']] = $group['articles'];
}

// セクション一覧を組み立て（カテゴリが 0 件でも新着は常に表示）
$sections = [];
$sections[] = [
    'title'    => '新着記事',
    'badge'    => '',
    'uid'      => 'swiper-latest',
    'articles' => $latest_articles,
    'href'     => '/articles',
];
foreach ($categories as $cat) {
    $href = match($cat['type'] ?? '') {
        'blog'    => '/blog',
        'youtube' => '/youtube',
        default   => '/articles?category_id=' . (int) $cat['id'],
    };
    $sections[] = [
        'title'    => $cat['name'],
        'badge'    => $cat['type'] ?? '',
        'uid'      => 'swiper-cat-' . (int) $cat['id'],
        'articles' => $articlesByCategory[(int) $cat['id']] ?? [],
        'href'     => $href,
    ];
}
?>

<?php foreach ($sections as $s): ?>
<section class="cat-section">
    <div class="cat-header">
        <h2 class="cat-title">
            <span class="badge <?= $this->h($s['badge']) ?>"><?= $this->h($s['title']) ?></span>
        </h2>
        <a href="<?= $this->h($s['href']) ?>" class="cat-more">もっと見る →</a>
    </div>
    <div class="cat-swiper-outer">
        <button class="cat-swiper-btn cat-swiper-prev" data-target="<?= $this->h($s['uid']) ?>" aria-label="前へ">&#8249;</button>
        <div class="swiper cat-swiper" id="<?= $this->h($s['uid']) ?>">
            <div class="swiper-wrapper">
                <?php if (empty($s['articles'])): ?>
                <div class="swiper-slide swiper-slide--empty">
                    <p class="no-articles-msg">まだ記事がありません</p>
                </div>
                <?php else: ?>
                <?php foreach ($s['articles'] as $a):
                    $thumb = $a['eye_catch_image'] ?? $a['youtube_thumbnail'] ?? null;
                    $date  = $a['published_at'] ?? $a['created_at'] ?? null;
                ?>
                <div class="swiper-slide">
                    <a href="/articles/<?= $this->h($a['slug']) ?>" class="carousel-card-link">
                        <div class="carousel-thumb">
                            <?php if ($thumb): ?>
                            <img src="<?= $this->h($thumb) ?>" alt="<?= $this->h($a['title']) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="carousel-no-thumb"><span>NO IMAGE</span></div>
                            <?php endif; ?>
                        </div>
                        <div class="carousel-info">
                            <p class="carousel-title"><?= $this->h($a['title']) ?></p>
                            <?php if ($date): ?>
                            <time class="carousel-date"><?= date('Y年m月d日', strtotime((string) $date)) ?></time>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <button class="cat-swiper-btn cat-swiper-next" data-target="<?= $this->h($s['uid']) ?>" aria-label="次へ">&#8250;</button>
    </div>
</section>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cat-swiper').forEach(function (el) {
        if (el.querySelector('.swiper-slide--empty')) return;
        var outer = el.closest('.cat-swiper-outer');
        new Swiper(el, {
            grabCursor: true,
            slidesPerView: 4,
            spaceBetween: 16,
            navigation: {
                prevEl: outer ? outer.querySelector('.cat-swiper-prev') : null,
                nextEl: outer ? outer.querySelector('.cat-swiper-next') : null,
            },
            breakpoints: {
                0:   { slidesPerView: 1.4, spaceBetween: 10 },
                481: { slidesPerView: 2.3, spaceBetween: 12 },
                769: { slidesPerView: 3,   spaceBetween: 14 },
                1024:{ slidesPerView: 4,   spaceBetween: 16 },
            },
        });
    });
});
</script>
