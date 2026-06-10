{{
/**
 * @var array<int, array<string, mixed>> $latest_articles
 * @var array<int, array{category: array<string, mixed>, articles: array<int, array<string, mixed>>}> $categories_with_articles
 * @var string $page_title
 */
$this->setLayout('layout');
$this->page_title = $page_title ?? 'ホーム';

$categoryUrl = static function (array $category): string {
    return match ($category['type'] ?? 'custom') {
        'blog'    => '/blog',
        'youtube' => '/youtube',
        default   => '/' . rawurlencode((string) $category['slug']),
    };
};

$articleUrl = static function (array $article): string {
    $prefix = ($article['category_type'] ?? 'custom') === 'blog' ? '/blog' : '/articles';
    return $prefix . '/' . rawurlencode((string) $article['slug']);
};

$sections = [];
if (!empty($latest_articles)) {
    $sections[] = [
        'title'       => '新着記事',
        'badge_class' => '',
        'url'         => '/articles',
        'articles'    => $latest_articles,
    ];
}
foreach ($categories_with_articles as $group) {
    $cat      = $group['category'];
    $articles = $group['articles'];
    if (!empty($articles)) {
        $sections[] = [
            'title'       => (string) $cat['name'],
            'badge_class' => (string) ($cat['type'] ?? ''),
            'url'         => $categoryUrl($cat),
            'articles'    => $articles,
        ];
    }
}
$swiperCount = count($sections);
}}

{{ foreach ($sections as $idx => $section): }}
<section class="cat-section">
    <div class="cat-header">
        <h2 class="cat-title">
            {{ if ($section['badge_class']): }}
            <span class="badge {{h $section['badge_class'] }}">{{h $section['title'] }}</span>
            {{ else: }}
            {{h $section['title'] }}
            {{ endif; }}
        </h2>
        <a href="{{h $section['url'] }}" class="cat-more">もっと見る &rarr;</a>
    </div>
    <div class="cat-swiper-outer">
        <button class="cat-swiper-btn" id="prev-{{= $idx }}">&#8249;</button>
        <div class="swiper cat-swiper" id="swiper-{{= $idx }}">
            <div class="swiper-wrapper">
                {{ foreach ($section['articles'] as $article): }}
                {{ $thumb = $article['eye_catch_image'] ?? $article['youtube_thumbnail'] ?? null; }}
                {{ $catType = $article['category_type'] ?? 'custom'; }}
                <div class="swiper-slide">
                    <a href="{{h $articleUrl($article) }}" class="carousel-card-link">
                        {{ if ($thumb): }}
                        <div class="carousel-thumb">
                            <img src="{{h $thumb }}"
                                 alt="{{h $article['title'] }}"
                                 loading="lazy">
                        </div>
                        {{ else: }}
                        <div class="carousel-no-thumb"><span>NO IMAGE</span></div>
                        {{ endif; }}
                        <div class="carousel-info">
                            {{ if (!empty($article['category_name'])): }}
                            <span class="badge {{h $catType }}"
                                  style="display:inline-block;margin-bottom:.25rem">
                                {{h $article['category_name'] }}
                            </span>
                            {{ endif; }}
                            <p class="carousel-title">{{h $article['title'] }}</p>
                            {{ if (!empty($article['published_at'])): }}
                            <time class="carousel-date" datetime="{{h $article['published_at'] }}">
                                {{h date('Y年m月d日', strtotime((string) $article['published_at'])) }}
                            </time>
                            {{ endif; }}
                        </div>
                    </a>
                </div>
                {{ endforeach; }}
            </div>
        </div>
        <button class="cat-swiper-btn" id="next-{{= $idx }}">&#8250;</button>
    </div>
</section>
{{ endforeach; }}

{{ if (empty($sections)): }}
<div class="no-articles-msg">まだ記事がありません。</div>
{{ endif; }}

<script>
(function () {
    var configs = [];
    {{ for ($i = 0; $i < $swiperCount; $i++): }}
    configs.push({ el: '#swiper-{{= $i }}', prev: '#prev-{{= $i }}', next: '#next-{{= $i }}' });
    {{ endfor; }}
    configs.forEach(function (c) {
        new Swiper(c.el, {
            slidesPerView: 1.4,
            spaceBetween: 12,
            navigation: { prevEl: c.prev, nextEl: c.next },
            breakpoints: {
                480:  { slidesPerView: 2.3 },
                768:  { slidesPerView: 3   },
                1024: { slidesPerView: 4   }
            }
        });
    });
}());
</script>
