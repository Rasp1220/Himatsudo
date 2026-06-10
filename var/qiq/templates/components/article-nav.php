<?php
/**
 * @var array<string, mixed>|null $prev
 * @var array<string, mixed>|null $next
 */
$navUrl = static function (array $a): string {
    $prefix = ($a['category_type'] ?? 'custom') === 'blog' ? '/blog' : '/articles';
    return $prefix . '/' . rawurlencode((string) $a['slug']);
};
?>
<?php if (!empty($prev) || !empty($next)): ?>
<nav class="article-nav" aria-label="前後の記事">
    <?php if (!empty($prev)): ?>
    <?php $prevThumb = $prev['eye_catch_image'] ?? $prev['youtube_thumbnail'] ?? null; ?>
    <a href="<?= $this->h($navUrl($prev)) ?>" class="article-nav-item article-nav-prev">
        <div class="article-nav-thumb">
            <?php if ($prevThumb): ?>
            <img src="<?= $this->h($prevThumb) ?>" alt="<?= $this->h($prev['title']) ?>" loading="lazy">
            <?php endif; ?>
        </div>
        <div class="article-nav-body">
            <div class="article-nav-label">&#8592; 前の記事</div>
            <div class="article-nav-title"><?= $this->h($prev['title']) ?></div>
        </div>
    </a>
    <?php else: ?>
    <div class="article-nav-item article-nav-prev article-nav-empty"></div>
    <?php endif; ?>

    <?php if (!empty($next)): ?>
    <?php $nextThumb = $next['eye_catch_image'] ?? $next['youtube_thumbnail'] ?? null; ?>
    <a href="<?= $this->h($navUrl($next)) ?>" class="article-nav-item article-nav-next">
        <div class="article-nav-thumb">
            <?php if ($nextThumb): ?>
            <img src="<?= $this->h($nextThumb) ?>" alt="<?= $this->h($next['title']) ?>" loading="lazy">
            <?php endif; ?>
        </div>
        <div class="article-nav-body">
            <div class="article-nav-label">次の記事 &#8594;</div>
            <div class="article-nav-title"><?= $this->h($next['title']) ?></div>
        </div>
    </a>
    <?php else: ?>
    <div class="article-nav-item article-nav-next article-nav-empty"></div>
    <?php endif; ?>
</nav>
<?php endif; ?>
