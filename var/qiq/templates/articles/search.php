{{
/**
 * @var string $q
 * @var array<int, array<string, mixed>> $articles
 * @var int $total
 * @var int $page
 * @var int $last_page
 * @var string $page_title
 */
$this->setLayout('layout');
$this->page_title = $page_title ?? 'サイト内検索';

$articleUrl = static function (array $article): string {
    $prefix = ($article['category_type'] ?? 'custom') === 'blog' ? '/blog' : '/articles';
    return $prefix . '/' . rawurlencode((string) $article['slug']);
};

$pageUrl = static function (int $p, string $q): string {
    return '/search?q=' . rawurlencode($q) . ($p > 1 ? '&page=' . $p : '');
};
}}

<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">サイト内検索</li>
    </ol>
</nav>

<h1 class="page-title">{{h $this->page_title }}</h1>

<div class="search-form-wrap">
    <form class="search-form-block" action="/search" method="get" role="search">
        <input type="search" name="q"
               value="{{h $q }}"
               placeholder="キーワードを入力…"
               aria-label="検索キーワード"
               class="search-input-block">
        <button type="submit" class="search-btn-block">検索</button>
    </form>
</div>

{{ if ($q !== ''): }}
<p class="search-summary">
    「<strong>{{h $q }}</strong>」の検索結果：{{= $total }}件
</p>
{{ endif; }}

{{ if (!empty($articles)): }}
<div class="search-results-list">
    {{ foreach ($articles as $article): }}
    {{ $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null; }}
    <a href="{{h $articleUrl($article) }}" class="search-card">
        <div class="search-card-thumb">
            {{ if ($thumb): }}
            <img src="{{h $thumb }}"
                 alt="{{h $article['title'] }}"
                 loading="lazy">
            {{ else: }}
            <span class="search-card-no-img">NO IMAGE</span>
            {{ endif; }}
        </div>
        <div class="search-card-body">
            {{ if (!empty($article['category_name'])): }}
            <span class="badge {{h $article['category_type'] ?? '' }}" style="display:inline-block;margin-bottom:.35rem;font-size:.7rem">
                {{h $article['category_name'] }}
            </span>
            {{ endif; }}
            <div class="search-card-title">{{h $article['title'] }}</div>
            {{ if (!empty($article['published_at'])): }}
            <div class="search-card-date">
                {{h date('Y年m月d日', strtotime((string) $article['published_at'])) }}
            </div>
            {{ endif; }}
        </div>
    </a>
    {{ endforeach; }}
</div>

{{ if ($last_page > 1): }}
<nav class="pagination" aria-label="ページネーション" style="margin-top:2rem">
    {{ if ($page > 1): }}
    <a href="{{h $pageUrl($page - 1, $q) }}">&laquo;</a>
    {{ endif; }}
    {{ for ($p = max(1, $page - 2); $p <= min($last_page, $page + 2); $p++): }}
    {{ if ($p === $page): }}
    <span class="current">{{= $p }}</span>
    {{ else: }}
    <a href="{{h $pageUrl($p, $q) }}">{{= $p }}</a>
    {{ endif; }}
    {{ endfor; }}
    {{ if ($page < $last_page): }}
    <a href="{{h $pageUrl($page + 1, $q) }}">&raquo;</a>
    {{ endif; }}
</nav>
{{ endif; }}

{{ elseif ($q !== ''): }}
<p class="no-articles-msg">該当する記事が見つかりませんでした。</p>
{{ endif; }}
