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

<?php foreach ($categories_with_articles as $idx => $group):
    $cat      = $group['category'];
    $articles = $group['articles'];
    $uid      = 'cat-' . (int) $cat['id'];
?>
<section class="cat-section" style="margin-bottom:3rem">
    <div class="cat-header">
        <h2 class="cat-title">
            <span class="badge <?= $this->h($cat['type'] ?? '') ?>"><?= $this->h($cat['name']) ?></span>
        </h2>
        <a href="/articles?category_id=<?= (int)$cat['id'] ?>" class="cat-more">もっと見る →</a>
    </div>

    <div class="carousel" id="<?= $uid ?>">
        <button class="carousel-btn prev" onclick="carouselMove('<?= $uid ?>', -1)" aria-label="前へ">&#8249;</button>
        <div class="carousel-viewport">
            <div class="carousel-track" id="<?= $uid ?>-track">
                <?php foreach ($articles as $article):
                    $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null;
                    $date  = $article['published_at'] ?? $article['created_at'] ?? null;
                ?>
                <div class="carousel-card">
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
                            <time class="carousel-date"><?= date('Y年m月d日', strtotime((string)$date)) ?></time>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="carousel-btn next" onclick="carouselMove('<?= $uid ?>', 1)" aria-label="次へ">&#8250;</button>
    </div>
</section>
<?php endforeach; ?>

<style>
.cat-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; }
.cat-title  { font-size:1.25rem; font-weight:700; }
.cat-more   { font-size:.875rem; color:#2563eb; font-weight:600; }
.cat-more:hover { text-decoration:underline; }

.carousel { position:relative; display:flex; align-items:center; gap:.5rem; }
.carousel-viewport { overflow:hidden; flex:1; }
.carousel-track { display:flex; gap:1rem; transition:transform .35s ease; will-change:transform; }

.carousel-card { flex:0 0 calc(33.333% - .667rem); min-width:0; }
@media(max-width:768px){ .carousel-card { flex:0 0 calc(50% - .5rem); } }
@media(max-width:480px){ .carousel-card { flex:0 0 100%; } }

.carousel-card-link { display:block; text-decoration:none; color:inherit; background:#fff;
    border-radius:.5rem; box-shadow:0 1px 3px rgba(0,0,0,.1); overflow:hidden;
    transition:transform .2s; }
.carousel-card-link:hover { transform:translateY(-3px); }

.carousel-thumb img { width:100%; height:180px; object-fit:cover; display:block; }
.carousel-no-thumb  { width:100%; height:180px; background:#e2e8f0; }

.carousel-info  { padding:.75rem; }
.carousel-title { font-size:.9rem; font-weight:600; line-height:1.4; margin-bottom:.4rem;
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.carousel-date  { font-size:.75rem; color:#64748b; }

.carousel-btn { flex-shrink:0; width:2rem; height:2rem; border-radius:50%; border:1px solid #e2e8f0;
    background:#fff; font-size:1.25rem; line-height:1; cursor:pointer; color:#334155;
    box-shadow:0 1px 2px rgba(0,0,0,.1); display:flex; align-items:center; justify-content:center; }
.carousel-btn:hover { background:#f1f5f9; }
</style>

<script>
(function() {
    var states = {};

    window.carouselMove = function(uid, dir) {
        var track = document.getElementById(uid + '-track');
        if (!track) return;
        var cards     = track.querySelectorAll('.carousel-card');
        var visible   = getVisible();
        var total     = cards.length;
        var maxIdx    = Math.max(0, total - visible);
        if (!states[uid]) states[uid] = 0;
        states[uid] = Math.min(maxIdx, Math.max(0, states[uid] + dir));
        var cardW = cards[0] ? cards[0].getBoundingClientRect().width + 16 : 0;
        track.style.transform = 'translateX(-' + (states[uid] * cardW) + 'px)';
    };

    function getVisible() {
        return window.innerWidth <= 480 ? 1 : window.innerWidth <= 768 ? 2 : 3;
    }

    // recalculate on resize
    window.addEventListener('resize', function() {
        Object.keys(states).forEach(function(uid) {
            var track = document.getElementById(uid + '-track');
            if (!track) return;
            var cards = track.querySelectorAll('.carousel-card');
            var visible = getVisible();
            var maxIdx  = Math.max(0, cards.length - visible);
            states[uid] = Math.min(states[uid], maxIdx);
            var cardW = cards[0] ? cards[0].getBoundingClientRect().width + 16 : 0;
            track.style.transform = 'translateX(-' + (states[uid] * cardW) + 'px)';
        });
    });
})();
</script>
