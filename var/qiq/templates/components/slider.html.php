<?php
/** @var array<int, array<string, mixed>> $sliderArticles */
$sliderArticles = $sliderArticles ?? [];
if (empty($sliderArticles)) {
    return;
}
?>
<section class="slider" style="margin-bottom:2.5rem">
    <div class="slider-track" id="slider-track">
        <?php foreach ($sliderArticles as $i => $article): ?>
        <div class="slider-item <?= $i === 0 ? 'active' : '' ?>" style="display:<?= $i === 0 ? 'block' : 'none' ?>">
            <a href="/articles/<?= $this->h($article['slug']) ?>" class="slider-link">
                <?php if (!empty($article['eye_catch_image']) || !empty($article['youtube_thumbnail'])): ?>
                <img
                    src="<?= $this->h($article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? '') ?>"
                    alt="<?= $this->h($article['title']) ?>"
                    style="width:100%;max-height:400px;object-fit:cover;border-radius:.5rem"
                >
                <?php endif; ?>
                <div style="padding:.75rem 0">
                    <?php if (!empty($article['category_name'])): ?>
                    <span class="badge <?= $this->h($article['category_type'] ?? '') ?>">
                        <?= $this->h($article['category_name']) ?>
                    </span>
                    <?php endif; ?>
                    <h2 style="font-size:1.25rem;font-weight:700;margin-top:.5rem">
                        <?= $this->h($article['title']) ?>
                    </h2>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if (count($sliderArticles) > 1): ?>
    <div style="display:flex;gap:.5rem;justify-content:center;margin-top:.75rem">
        <?php foreach ($sliderArticles as $i => $a): ?>
        <button
            onclick="showSlide(<?= $i ?>)"
            id="dot-<?= $i ?>"
            style="width:10px;height:10px;border-radius:50%;border:none;background:<?= $i === 0 ? '#2563eb' : '#cbd5e1' ?>;cursor:pointer"
        ></button>
        <?php endforeach; ?>
    </div>
    <script>
    var currentSlide = 0;
    var items = document.querySelectorAll('.slider-item');
    var dots  = document.querySelectorAll('[id^=dot-]');
    function showSlide(n) {
        items[currentSlide].style.display = 'none';
        dots[currentSlide].style.background = '#cbd5e1';
        currentSlide = n;
        items[currentSlide].style.display = 'block';
        dots[currentSlide].style.background = '#2563eb';
    }
    setInterval(function() { showSlide((currentSlide + 1) % items.length); }, 5000);
    </script>
    <?php endif; ?>
</section>
