<?php
/** @var array<string, mixed> $article */
$this->setLayout('layout');
$this->page_title = $article['title'] ?? '';
?>
<article style="max-width:780px;margin:0 auto">
    <?php if (!empty($article['category_name'])): ?>
    <div style="margin-bottom:.75rem">
        <a href="/articles?category_id=<?= (int) $article['category_id'] ?>" class="badge <?= $this->h($article['category_type'] ?? '') ?>">
            <?= $this->h($article['category_name']) ?>
        </a>
    </div>
    <?php endif; ?>

    <h1 style="font-size:2rem;font-weight:700;line-height:1.3;margin-bottom:1rem">
        <?= $this->h($article['title']) ?>
    </h1>

    <div style="display:flex;gap:1rem;color:#64748b;font-size:.875rem;margin-bottom:1.5rem">
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
         style="width:100%;max-height:450px;object-fit:cover;border-radius:.5rem;margin-bottom:2rem">
    <?php endif; ?>

    <div class="article-body" style="
        font-size:1rem;line-height:1.8;
        h1,h2,h3{font-weight:700;margin:1.5em 0 .5em}
        p{margin-bottom:1em}
        img{max-width:100%;border-radius:.375rem;margin:1em 0}
        a{color:#2563eb}
        pre{background:#f1f5f9;padding:1em;border-radius:.375rem;overflow-x:auto}
        code{background:#f1f5f9;padding:.1em .3em;border-radius:.2rem;font-size:.9em}
        blockquote{border-left:4px solid #e2e8f0;padding-left:1em;color:#64748b;margin:1em 0}
        ul,ol{padding-left:1.5em;margin-bottom:1em}
        table{border-collapse:collapse;width:100%}
        th,td{border:1px solid #e2e8f0;padding:.5em .75em}
    ">
        <?= $article['content'] ?? '' /* Rich-text HTML from CMS; CMS is responsible for sanitising */ ?>
    </div>

    <div style="margin-top:3rem;padding-top:1.5rem;border-top:1px solid #e2e8f0">
        <a href="/articles" style="color:#64748b;font-size:.875rem">&larr; 記事一覧に戻る</a>
    </div>
</article>
