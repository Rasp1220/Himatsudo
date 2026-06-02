<?php
/**
 * @var array<int, array{category: array<string,mixed>, articles: array<int,array<string,mixed>>}> $categories_with_articles
 * @var array<int, array<string,mixed>> $latest_articles
 * @var array<int, array<string,mixed>> $regular_articles
 */
$this->setLayout('layout');
$this->page_title = 'ホーム';

if (empty($categories_with_articles) && empty($latest_articles)):
?>
<p style="color:#64748b;padding:3rem 0;text-align:center">まだ記事がありません。</p>
<?php return; endif;

// ─── Helper: スライドカードを出力 ───────────────────────────
$renderSlide = function(array $article) use ($that): void {
    $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null;
    $date  = $article['published_at'] ?? $article['created_at'] ?? null;
    echo '<div class="swiper-slide">';
    echo '<a href="/articles/' . htmlspecialchars((string)($article['slug'] ?? ''), ENT_QUOTES) . '" class="carousel-card-link">';
    echo '<div class="carousel-thumb">';
    if ($thumb) {
        echo '<img src="' . htmlspecialchars((string)$thumb, ENT_QUOTES) . '" alt="' . htmlspecialchars((string)($article['title'] ?? ''), ENT_QUOTES) . '" loading="lazy">';
    } else {
        echo '<div class="carousel-no-thumb"></div>';
    }
    echo '</div>';
    echo '<div class="carousel-info">';
    echo '<p class="carousel-title">' . htmlspecialchars((string)($article['title'] ?? ''), ENT_QUOTES) . '</p>';
    if ($date) {
        echo '<time class="carousel-date">' . date('Y年m月d日', strtotime((string)$date)) . '</time>';
    }
    echo '</div></a></div>';
};

// ─── Helper: セクション全体を出力 ───────────────────────────
$renderSection = function(string $title, string $badgeClass, string $uid, array $articles, string $moreHref) use ($renderSlide): void {
    if (empty($articles)) return;
    echo '<section class="cat-section">';
    echo '<div class="cat-header">';
    echo '<h2 class="cat-title"><span class="badge ' . htmlspecialchars($badgeClass, ENT_QUOTES) . '">' . htmlspecialchars($title, ENT_QUOTES) . '</span></h2>';
    echo '<a href="' . htmlspecialchars($moreHref, ENT_QUOTES) . '" class="cat-more">もっと見る →</a>';
    echo '</div>';
    echo '<div class="cat-swiper-outer">';
    echo '<button class="cat-swiper-btn cat-swiper-prev" data-target="' . htmlspecialchars($uid, ENT_QUOTES) . '" aria-label="前へ">&#8249;</button>';
    echo '<div class="swiper cat-swiper" id="' . htmlspecialchars($uid, ENT_QUOTES) . '"><div class="swiper-wrapper">';
    foreach ($articles as $article) {
        $renderSlide($article);
    }
    echo '</div></div>';
    echo '<button class="cat-swiper-btn cat-swiper-next" data-target="' . htmlspecialchars($uid, ENT_QUOTES) . '" aria-label="次へ">&#8250;</button>';
    echo '</div></section>';
};
?>

<?php $renderSection('新着記事', '', 'swiper-latest', $latest_articles, '/articles'); ?>

<?php $renderSection('通常記事', 'blog', 'swiper-regular', $regular_articles, '/articles?type=regular'); ?>

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
                            <img src="<?= $this->h($thumb) ?>" alt="<?= $this->h($article['title']) ?>" loading="lazy">
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
            grabCursor: true,
            slidesPerView: 10,
            spaceBetween: 12,
            navigation: {
                prevEl: outer ? outer.querySelector('.cat-swiper-prev') : null,
                nextEl: outer ? outer.querySelector('.cat-swiper-next') : null,
            },
            breakpoints: {
                0:    { slidesPerView: 2, spaceBetween: 8  },
                481:  { slidesPerView: 4, spaceBetween: 10 },
                769:  { slidesPerView: 7, spaceBetween: 10 },
                1024: { slidesPerView: 10, spaceBetween: 12 },
            },
        });
    });
});
</script>
