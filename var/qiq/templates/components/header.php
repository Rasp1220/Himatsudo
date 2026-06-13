<header class="site-header">
    <div class="container">
        <a href="/" class="site-title"><span></span>ひまつど</a>
        <div class="header-right">
            <nav class="site-nav-desktop">
                <ul class="site-nav">
                    <li><a href="/articles">記事一覧</a></li>
                    <li><a href="/blog">ブログ</a></li>
                    <li><a href="/youtube">YouTube</a></li>
                    <li><a href="/staff">運営一覧</a></li>
                </ul>
            </nav>
            <form class="header-search" action="/search" method="get" role="search">
                <input type="search" name="q" placeholder="サイト内検索…" aria-label="サイト内検索">
                <button type="submit" aria-label="検索">&#128269;</button>
            </form>
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
    <form class="hamburger-search" action="/search" method="get" role="search">
        <input type="search" name="q" placeholder="サイト内検索…" aria-label="サイト内検索">
        <button type="submit" aria-label="検索">&#128269;</button>
    </form>
    <ul>
        <li><a href="/articles">記事一覧</a></li>
        <li><a href="/blog">ブログ</a></li>
        <li><a href="/youtube">YouTube</a></li>
        <li><a href="/staff">運営一覧</a></li>
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
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });
}());
</script>
