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

    <!-- ナビボタンを外側ラッパーに配置して overflow: hidden の影響を避ける -->
    <div class="cat-swiper-outer">
        <button class="cat-swiper-btn cat-swiper-prev" data-target="<?= $uid ?>" aria-label="前へ">&#8249;</button>

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
        </div>

        <button class="cat-swiper-btn cat-swiper-next" data-target="<?= $uid ?>" aria-label="次へ">&#8250;</button>
    </div>
</section>
<?php endforeach; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.cat-swiper').forEach(function (el) {
        var outer = el.closest('.cat-swiper-outer');
        new Swiper(el, {
            slidesPerView: 3,
            spaceBetween: 16,
            navigation: {
                prevEl: outer ? outer.querySelector('.cat-swiper-prev') : null,
                nextEl: outer ? outer.querySelector('.cat-swiper-next') : null,
            },
            breakpoints: {
                0:   { slidesPerView: 1, spaceBetween: 12 },
                481: { slidesPerView: 2, spaceBetween: 14 },
                769: { slidesPerView: 3, spaceBetween: 16 },
            },
        });
    });
});
</script>
