{{
/**
 * @var array<string, mixed>|null $prev
 * @var array<string, mixed>|null $next
 * @var array<string, mixed>|null $author_context
 */
$navAuthorId = !empty($author_context) ? (int) $author_context['id'] : null;
$navUrl = function (array $a) use ($navAuthorId): string {
    // 運営プロフィール経由の場合は運営別の記事URLに合わせる
    if ($navAuthorId !== null) {
        return '/author/' . $navAuthorId . '/' . rawurlencode((string) $a['slug']);
    }
    $prefix = ($a['category_type'] ?? 'custom') === 'blog' ? '/blog' : '/articles';
    return $prefix . '/' . rawurlencode((string) $a['slug']);
};
}}
{{ if (!empty($prev) || !empty($next)): }}
<nav class="article-nav" aria-label="前後の記事">
    {{ if (!empty($prev)): }}
    {{ $prevThumb = $prev['eye_catch_image'] ?? $prev['youtube_thumbnail'] ?? null; }}
    <a href="{{h $navUrl($prev) }}" class="article-nav-item article-nav-prev">
        <div class="article-nav-thumb">
            {{ if ($prevThumb): }}
            <img src="{{h $prevThumb }}" alt="{{h $prev['title'] }}" loading="lazy">
            {{ endif; }}
        </div>
        <div class="article-nav-body">
            <div class="article-nav-label">&#8592; 前の記事</div>
            <div class="article-nav-title">{{h $prev['title'] }}</div>
        </div>
    </a>
    {{ else: }}
    <div class="article-nav-item article-nav-prev article-nav-empty"></div>
    {{ endif; }}

    {{ if (!empty($next)): }}
    {{ $nextThumb = $next['eye_catch_image'] ?? $next['youtube_thumbnail'] ?? null; }}
    <a href="{{h $navUrl($next) }}" class="article-nav-item article-nav-next">
        <div class="article-nav-thumb">
            {{ if ($nextThumb): }}
            <img src="{{h $nextThumb }}" alt="{{h $next['title'] }}" loading="lazy">
            {{ endif; }}
        </div>
        <div class="article-nav-body">
            <div class="article-nav-label">次の記事 &#8594;</div>
            <div class="article-nav-title">{{h $next['title'] }}</div>
        </div>
    </a>
    {{ else: }}
    <div class="article-nav-item article-nav-next article-nav-empty"></div>
    {{ endif; }}
</nav>
{{ endif; }}
