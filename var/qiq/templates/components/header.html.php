<header class="site-header">
    <div class="container">
        <a href="/" class="site-title">ひまつど</a>
        <div class="header-right">
            <nav class="site-nav-desktop">
                <ul class="site-nav">
                    <li><a href="/articles">記事一覧</a></li>
                    <?php foreach ($categories ?? [] as $cat): ?>
                        <?php if (in_array($cat['type'], ['blog', 'youtube'], true)): ?>
                        <?php $catUrl = $cat['type'] === 'blog' ? '/blog' : '/youtube'; ?>
                        <li>
                            <a href="<?= $this->h($catUrl) ?>">
                                <?= $this->h($cat['name']) ?>
                            </a>
                        </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="メニューを開く" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<div class="hamburger-overlay" id="hamburgerOverlay"></div>
<nav class="hamburger-menu" id="hamburgerMenu" aria-label="サイトメニュー">
    <button class="hamburger-close" id="hamburgerClose" aria-label="メニューを閉じる">&#x2715;</button>
    <ul>
        <li><a href="/articles">記事一覧</a></li>
        <?php foreach ($categories ?? [] as $cat): ?>
            <?php if (in_array($cat['type'], ['blog', 'youtube'], true)): ?>
                <?php $catUrl = $cat['type'] === 'blog' ? '/blog' : '/youtube'; ?>
            <li>
                <a href="<?= $this->h($catUrl) ?>">
                    <?= $this->h($cat['name']) ?>
                </a>
            </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</nav>

<script>
(function () {
    var btn     = document.getElementById('hamburgerBtn');
    var menu    = document.getElementById('hamburgerMenu');
    var overlay = document.getElementById('hamburgerOverlay');
    var close   = document.getElementById('hamburgerClose');

    function openMenu() {
        menu.classList.add('is-open');
        overlay.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closeMenu() {
        menu.classList.remove('is-open');
        overlay.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    btn.addEventListener('click', openMenu);
    overlay.addEventListener('click', closeMenu);
    close.addEventListener('click', closeMenu);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeMenu();
    });
}());
</script>
