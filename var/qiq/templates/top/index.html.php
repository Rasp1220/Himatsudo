<?php
/**
 * @var array<int,array<string,mixed>>                                                  $latest_articles
 * @var array<int,array<string,mixed>>                                                  $regular_articles
 * @var array<int,array{category:array<string,mixed>,articles:array<int,array<string,mixed>>}> $categories_with_articles
 */
$this->setLayout('layout');
$this->page_title = 'ホーム';

// ─── セクション一覧を組み立て ─────────────────────────────────
$sections = [];

if (!empty($latest_articles)) {
    $sections[] = ['title' => '新着記事', 'badge' => '',     'uid' => 'swiper-latest',  'articles' => $latest_articles,  'href' => '/articles'];
}
if (!empty($regular_articles)) {
    $sections[] = ['title' => '通常記事', 'badge' => 'blog', 'uid' => 'swiper-regular', 'articles' => $regular_articles, 'href' => '/articles'];
}
foreach ($categories_with_articles as $group) {
    $cat      = $group['category'];
    $sections[] = [
        'title'    => $cat['name'],
        'badge'    => $cat['type'] ?? '',
        'uid'      => 'swiper-cat-' . (int) $cat['id'],
        'articles' => $group['articles'],
        'href'     => '/articles?category_id=' . (int) $cat['id'],
    ];
}

if (empty($sections)): ?>
<p style="color:#64748b;padding:3rem 0;text-align:center">まだ記事がありません。</p>
<?php return; endif; ?>

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
                            <div class="carousel-no-thumb"></div>
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
            </div>
        </div>
        <button class="cat-swiper-btn cat-swiper-next" data-target="<?= $this->h($s['uid']) ?>" aria-label="次へ">&#8250;</button>
    </div>
</section>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cat-swiper').forEach(function (el) {
        var outer = el.closest('.cat-swiper-outer');
        new Swiper(el, {
            grabCursor: true,
            slidesPerView: 10,
            spaceBetween: 8,
            navigation: {
                prevEl: outer ? outer.querySelector('.cat-swiper-prev') : null,
                nextEl: outer ? outer.querySelector('.cat-swiper-next') : null,
            },
        });
    });
});
</script>
