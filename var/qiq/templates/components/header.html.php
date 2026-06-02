<header class="site-header">
    <div class="container">
        <a href="/" class="site-title">Himatsudo</a>
        <nav>
            <ul class="site-nav">
                <li><a href="/">ホーム</a></li>
                <li><a href="/articles">記事一覧</a></li>
                <?php foreach ($this->categories ?? [] as $cat): ?>
                    <?php if (in_array($cat['type'], ['blog', 'youtube'], true)): ?>
                    <li>
                        <a href="/articles?category_id=<?= (int) $cat['id'] ?>">
                            <?= $this->h($cat['name']) ?>
                        </a>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>
