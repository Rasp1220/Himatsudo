{{
/**
 * @var array<int, array<string, mixed>> $articles
 * @var array<int, array<string, mixed>> $categories
 * @var array<string, mixed>|null        $current_category
 * @var int      $page
 * @var int      $last_page
 * @var int      $total
 * @var int|null $category_id
 * @var string   $page_title
 * @var string|null $list_base_url   set by Blog / Youtube pages
 * @var string|null $category_slug   set by Category pages
 */
$this->setLayout('layout');
$this->page_title = $page_title ?? '記事一覧';

if (!empty($list_base_url)) {
    $baseUrl = (string) $list_base_url;
} elseif (!empty($category_slug)) {
    $baseUrl = '/' . rawurlencode((string) $category_slug);
} else {
    $baseUrl = '/articles';
}

$pageUrl = static function (int $p, string $base, ?int $catId): string {
    $params = [];
    if ($catId !== null && $base === '/articles') {
        $params[] = 'category_id=' . $catId;
    }
    if ($p > 1) {
        $params[] = 'page=' . $p;
    }
    return $base . ($params ? '?' . implode('&', $params) : '');
};

$categoryUrl = static function (array $cat): string {
    return match ($cat['type'] ?? 'custom') {
        'blog'    => '/blog',
        'youtube' => '/youtube',
        default   => '/' . rawurlencode((string) $cat['slug']),
    };
};

$articleUrl = static function (array $article): string {
    $prefix = ($article['category_type'] ?? 'custom') === 'blog' ? '/blog' : '/articles';
    return $prefix . '/' . rawurlencode((string) $article['slug']);
};
}}

<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        {{ if (!empty($list_base_url) && $list_base_url === '/blog'): }}
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">ブログ一覧</li>
        {{ elseif (!empty($list_base_url) && $list_base_url === '/youtube'): }}
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">YouTube</li>
        {{ elseif (!empty($current_category)): }}
        <li class="breadcrumb-item"><a href="/articles">記事一覧</a></li>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">
            {{h (string) ($current_category['name'] ?? $page_title) }}
        </li>
        {{ else: }}
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">記事一覧</li>
        {{ endif; }}
    </ol>
</nav>

<h1 class="page-title">{{h $this->page_title }}</h1>

<div class="content-layout">
    <div class="content-main">
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
                        <img src="{{h $thumb }}"
                             alt="{{h $article['title'] }}"
                             class="card-img"
                             loading="lazy">
                        {{ else: }}
                        <span class="card-no-img">NO IMAGE</span>
                        {{ endif; }}
                    </div>
                    <div class="card-body">
                        {{ if (!empty($article['category_name'])): }}
                        <span class="badge {{h $catType }}"
                              style="display:inline-block;margin-bottom:.4rem">
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
            <a href="{{h $pageUrl($page - 1, $baseUrl, $category_id ?? null) }}">&laquo;</a>
            {{ endif; }}
            {{ for ($p = max(1, $page - 2); $p <= min($last_page, $page + 2); $p++): }}
            {{ if ($p === $page): }}
            <span class="current">{{= $p }}</span>
            {{ else: }}
            <a href="{{h $pageUrl($p, $baseUrl, $category_id ?? null) }}">{{= $p }}</a>
            {{ endif; }}
            {{ endfor; }}
            {{ if ($page < $last_page): }}
            <a href="{{h $pageUrl($page + 1, $baseUrl, $category_id ?? null) }}">&raquo;</a>
            {{ endif; }}
        </nav>
        {{ endif; }}

        {{ else: }}
        <div class="no-articles-msg">まだ記事がありません。</div>
        {{ endif; }}
    </div>

    {{ if (!empty($categories)): }}
    <aside class="content-aside sidebar">
        <h3>カテゴリ</h3>
        <ul>
            <li>
                <a href="/articles"{{= (empty($category_id) && empty($list_base_url) && empty($category_slug)) ? ' class="active"' : '' }}>すべて</a>
            </li>
            {{ foreach ($categories as $cat): }}
            {{
                $catUrl   = $categoryUrl($cat);
                $isActive = (!empty($category_id) && (int) ($cat['id'] ?? 0) === (int) $category_id)
                         || (!empty($list_base_url) && $catUrl === (string) $list_base_url)
                         || (!empty($category_slug) && ($cat['slug'] ?? '') === (string) $category_slug);
            }}
            <li>
                <a href="{{h $catUrl }}"{{= $isActive ? ' class="active"' : '' }}>
                    {{h (string) $cat['name'] }}
                </a>
            </li>
            {{ endforeach; }}
        </ul>
    </aside>
    {{ endif; }}
</div>
