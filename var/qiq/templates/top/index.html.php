<?php
/**
 * @var array<int, array{category: array<string,mixed>, articles: array<int,array<string,mixed>>}> $categories_with_articles
 */
$this->setLayout('layout');
$this->page_title = 'ホーム';

if (empty($categories_with_articles)):
?>
<p style="color:#64748b;padding:3rem 0;text-align:center">まだ記事がありません。</p>
<?php return; endif; ?>

<?php foreach ($categories_with_articles as $group):
    $cat      = $group['category'];
    $articles = $group['articles'];
    $uid      = 'swiper-cat-' . (int) $cat['id'];
?>
<section class="cat-section">
    <div class="cat-header">
        <h2 class="cat-title">
            <span class="badge <?= $this->h($cat['type'] ?? '') ?>"><?= $this->h($cat['name']) ?></span>
        </h2>
        <a href="/articles?category_id=<?= (int) $cat['id'] ?>" class="cat-more">もっと見る →</a>
    </div>

    <div class="swiper cat-swiper" id="<?= $uid ?>">
        <div class="swiper-wrapper">
            <?php foreach ($articles as $article):
                $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null;
                $date  = $article['published_at'] ?? $article['created_at'] ?? null;
            ?>
            <div class="swiper-slide">
                <a href="/articles/<?= $this->h($article['slug']) ?>" class="carousel-card-link">
                    <div class="carousel-thumb">
                        <?php if ($thumb): ?>
                        <img src="<?= $this->h($thumb) ?>" alt="<?= $this->h($article['title']) ?>">
                        <?php else: ?>
                        <div class="carousel-no-thumb"></div>
                        <?php endif; ?>
                    </div>
                    <div class="carousel-info">
                        <p class="carousel-title"><?= $this->h($article['title']) ?></p>
                        <?php if ($date): ?>
                        <time class="carousel-date"><?= date('Y年m月d日', strtotime((string) $date)) ?></time>
                        <?php endif; ?>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="swiper-button-prev cat-swiper-prev"></div>
        <div class="swiper-button-next cat-swiper-next"></div>
    </div>
</section>
<?php endforeach; ?>

<script>
(function () {
    document.querySelectorAll('.cat-swiper').forEach(function (el) {
        new Swiper(el, {
            slidesPerView: 3,
            spaceBetween: 16,
            navigation: {
                prevEl: el.querySelector('.cat-swiper-prev'),
                nextEl: el.querySelector('.cat-swiper-next'),
            },
            breakpoints: {
                0:   { slidesPerView: 1, spaceBetween: 12 },
                481: { slidesPerView: 2, spaceBetween: 14 },
                769: { slidesPerView: 3, spaceBetween: 16 },
            },
        });
    });
}());
</script>
