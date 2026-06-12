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
