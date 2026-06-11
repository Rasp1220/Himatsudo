{{
/**
 * @var array<int, array<string, mixed>> $related_articles
 */
$articleUrl = function (array $a): string {
    $prefix = ($a['category_type'] ?? 'custom') === 'blog' ? '/blog' : '/articles';
    return $prefix . '/' . rawurlencode((string) $a['slug']);
};
}}
{{ if (!empty($related_articles)): }}
<section class="related-articles">
    <h2 class="related-articles-title">関連記事</h2>
    <div class="related-articles-grid">
        {{ foreach ($related_articles as $related): }}
            {{
                $thumb   = $related['eye_catch_image'] ?? $related['youtube_thumbnail'] ?? null;
                $catType = $related['category_type'] ?? 'custom';
            }}
            <article class="card">
                <a href="{{h $articleUrl($related) }}">
                    <div class="card-thumb">
                        {{ if ($thumb): }}
                            <img src="{{h $thumb }}"
                                 alt="{{h $related['title'] }}"
                                 class="card-img"
                                 loading="lazy">
                        {{ else: }}
                            <span class="card-no-img">NO IMAGE</span>
                        {{ endif; }}
                    </div>
                    <div class="card-body">
                        {{ if (!empty($related['category_name'])): }}
                            <span class="badge {{h $catType }}"
                                  style="display:inline-block;margin-bottom:.4rem">
                                {{h $related['category_name'] }}
                            </span>
                        {{ endif; }}
                        <h3 class="card-title">{{h $related['title'] }}</h3>
                        {{ if (!empty($related['published_at'])): }}
                            <p class="card-meta" style="margin-top:.4rem">
                                {{h date('Y年m月d日', strtotime((string) $related['published_at'])) }}
                            </p>
                        {{ endif; }}
                    </div>
                </a>
            </article>
        {{ endforeach; }}
    </div>
</section>
{{ endif; }}
