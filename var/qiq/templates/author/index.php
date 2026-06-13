{{
    /**
     * @var array<string, mixed> $author
     * @var array<int, array<string, mixed>> $articles
     * @var int $page
     * @var int $last_page
     * @var int $total
     * @var string $list_base_url
     */
    $this->setLayout('layout');
    $this->page_title = (string) ($author['name'] ?? '運営');

    $authorId = (int) $author['id'];

    // 運営別ブログ一覧。記事リンク・前後遷移は運営別URLに揃える。
    $articleUrl = function (array $article) use ($authorId): string {
        return '/author/' . $authorId . '/' . rawurlencode((string) $article['slug']);
    };

    $pageUrl = function (int $p) use ($authorId): string {
        return '/author/' . $authorId . ($p > 1 ? '?page=' . $p : '');
    };
}}

<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        <li class="breadcrumb-item"><a href="/staff">運営一覧</a></li>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">{{h $author['name'] }}</li>
    </ol>
</nav>

<section class="author-profile">
    <div class="author-profile-avatar">
        {{ if (!empty($author['avatar'])): }}
            <img src="{{h $author['avatar'] }}" alt="{{h $author['name'] }}">
        {{ else: }}
            <span class="staff-avatar-fallback">{{h mb_substr((string) $author['name'], 0, 1) }}</span>
        {{ endif; }}
    </div>
    <div class="author-profile-info">
        <h1 class="author-profile-name">{{h $author['name'] }}</h1>
        {{ if (!empty($author['bio'])): }}
            <p class="author-profile-bio">{{= nl2br(htmlspecialchars((string) $author['bio'], ENT_QUOTES)) }}</p>
        {{ endif; }}
        {{ if (!empty($author['instagram_url']) || !empty($author['twitter_url']) || !empty($author['tiktok_url'])): }}
        <div class="author-sns-links">
            {{ if (!empty($author['instagram_url'])): }}
            <a href="{{h $author['instagram_url'] }}" class="author-sns-link author-sns-instagram" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
            {{ endif; }}
            {{ if (!empty($author['twitter_url'])): }}
            <a href="{{h $author['twitter_url'] }}" class="author-sns-link author-sns-x" target="_blank" rel="noopener noreferrer" aria-label="X (Twitter)">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.742l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            {{ endif; }}
            {{ if (!empty($author['tiktok_url'])): }}
            <a href="{{h $author['tiktok_url'] }}" class="author-sns-link author-sns-tiktok" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.79a8.18 8.18 0 004.78 1.52V6.85a4.85 4.85 0 01-1.01-.16z"/></svg>
            </a>
            {{ endif; }}
        </div>
        {{ endif; }}
    </div>
</section>

<h2 class="page-title">{{h $author['name'] }}の記事</h2>

{{ if (!empty($articles)): }}
    <div class="articles-grid">
        {{ foreach ($articles as $article): }}
            {{
                $thumb   = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null;
                $catType = $article['category_type'] ?? 'custom';
            }}
            <article class="card">
                <a href="{{h $articleUrl($article) }}">
                    <div class="card-thumb">
                        {{ if ($thumb): }}
                            <img src="{{h $thumb }}" alt="{{h $article['title'] }}" class="card-img" loading="lazy">
                        {{ else: }}
                            <span class="card-no-img">NO IMAGE</span>
                        {{ endif; }}
                    </div>
                    <div class="card-body">
                        {{ if (!empty($article['category_name'])): }}
                            <span class="badge {{h $catType }}" style="display:inline-block;margin-bottom:.4rem">
                                {{h $article['category_name'] }}
                            </span>
                        {{ endif; }}
                        <h2 class="card-title">{{h $article['title'] }}</h2>
                        {{ if (!empty($article['excerpt'])): }}
                            <p style="font-size:.875rem;color:#475569;margin-top:.4rem">
                                {{h mb_strimwidth((string) $article['excerpt'], 0, 60, '…') }}
                            </p>
                        {{ endif; }}
                        {{ if (!empty($article['published_at'])): }}
                            <p class="card-meta" style="margin-top:.4rem">
                                {{h date('Y年m月d日', strtotime((string) $article['published_at'])) }}
                            </p>
                        {{ endif; }}
                    </div>
                </a>
            </article>
        {{ endforeach; }}
    </div>

    {{ if (($last_page ?? 1) > 1): }}
        <nav class="pagination" aria-label="ページネーション">
            {{ if ($page > 1): }}
                <a href="{{h $pageUrl($page - 1) }}">&laquo;</a>
            {{ endif; }}
            {{ for ($p = max(1, $page - 2); $p <= min($last_page, $page + 2); $p++): }}
                {{ if ($p === $page): }}
                    <span class="current">{{= $p }}</span>
                {{ else: }}
                    <a href="{{h $pageUrl($p) }}">{{= $p }}</a>
                {{ endif; }}
            {{ endfor; }}
            {{ if ($page < $last_page): }}
                <a href="{{h $pageUrl($page + 1) }}">&raquo;</a>
            {{ endif; }}
        </nav>
    {{ endif; }}
{{ else: }}
    <div class="no-articles-msg">まだ記事がありません。</div>
{{ endif; }}
