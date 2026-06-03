<?php
/**
 * @var array<string, mixed> $article
 */
$this->setLayout('layout');
$this->page_title = $article['title'] ?? '';

$blocks   = null;
$richHtml = null;
if (!empty($article['blocks'])) {
    $decoded = json_decode((string) $article['blocks'], true);
    if (is_array($decoded) && count($decoded) > 0) {
        $blocks = $decoded;           // legacy block-editor JSON
    } else {
        $richHtml = (string) $article['blocks'];  // TinyMCE HTML stored in blocks field
    }
}
if ($richHtml === null && !empty($article['content'])) {
    $richHtml = (string) $article['content'];
}
?>
<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        <li class="breadcrumb-item"><a href="/articles">記事一覧</a></li>
        <?php if (!empty($article['category_name'])): ?>
        <li class="breadcrumb-item">
            <a href="/articles?category_id=<?= (int) $article['category_id'] ?>">
                <?= $this->h($article['category_name']) ?>
            </a>
        </li>
        <?php endif; ?>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">
            <?= $this->h(mb_strimwidth((string) $article['title'], 0, 40, '…')) ?>
        </li>
    </ol>
</nav>

<article class="article-detail">
    <?php if (!empty($article['category_name'])): ?>
    <div class="article-category">
        <a href="/articles?category_id=<?= (int) $article['category_id'] ?>" class="badge <?= $this->h($article['category_type'] ?? '') ?>">
            <?= $this->h($article['category_name']) ?>
        </a>
    </div>
    <?php endif; ?>

    <h1 class="article-title"><?= $this->h($article['title']) ?></h1>

    <div class="article-meta">
        <?php if (!empty($article['author_name'])): ?>
        <span>by <?= $this->h($article['author_name']) ?></span>
        <?php endif; ?>
        <?php if (!empty($article['published_at'])): ?>
        <span><?= $this->h(date('Y年m月d日', strtotime((string) $article['published_at']))) ?></span>
        <?php endif; ?>
    </div>

    <?php if (!empty($article['eye_catch_image'])): ?>
    <img src="<?= $this->h($article['eye_catch_image']) ?>"
         alt="<?= $this->h($article['title']) ?>"
         class="article-eyecatch">
    <?php endif; ?>

    <?php if ($blocks !== null): ?>
    <div class="article-blocks">
        <?php foreach ($blocks as $block):
            $type = $block['type'] ?? '';
        ?>
        <?php if ($type === 'heading'):
            $level = (int) ($block['level'] ?? 2);
            $level = max(2, min(4, $level));
            $tag   = 'h' . $level;
        ?>
        <<?= $tag ?> class="block-heading block-heading-<?= $level ?>">
            <?= $this->h($block['text'] ?? '') ?>
        </<?= $tag ?>>

        <?php elseif ($type === 'text'): ?>
        <div class="article-body block-text">
            <?= $block['html'] ?? '' /* Rich-text HTML from CMS; CMS is responsible for sanitising */ ?>
        </div>

        <?php elseif ($type === 'image' && !empty($block['url'])): ?>
        <figure class="block-image">
            <img src="<?= $this->h($block['url']) ?>"
                 alt="<?= $this->h($block['alt'] ?? '') ?>"
                 class="block-image-img">
            <?php if (!empty($block['caption'])): ?>
            <figcaption class="block-image-caption"><?= $this->h($block['caption']) ?></figcaption>
            <?php endif; ?>
        </figure>

        <?php elseif ($type === 'video' && !empty($block['video_id'])): ?>
        <figure class="block-video">
            <div class="block-video-wrapper">
                <iframe
                    src="https://www.youtube.com/embed/<?= $this->h($block['video_id']) ?>"
                    title="<?= $this->h($block['caption'] ?? 'YouTube') ?>"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="block-video-iframe">
                </iframe>
            </div>
            <?php if (!empty($block['caption'])): ?>
            <figcaption class="block-video-caption"><?= $this->h($block['caption']) ?></figcaption>
            <?php endif; ?>
        </figure>

        <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php elseif ($richHtml !== null): ?>
    <div class="article-body tinymce-content">
        <?= $richHtml ?>
    </div>

    <?php else: ?>
    <div class="article-body">
        <!-- 本文なし -->
    </div>
    <?php endif; ?>

    <div class="article-footer">
        <a href="/articles">&larr; 記事一覧に戻る</a>
    </div>
</article>
