{{
    /**
     * @var array<string, mixed> $article
     * @var array<string, mixed>|null $prev
     * @var array<string, mixed>|null $next
     */
    $this->setLayout('layout');
    $this->page_title = $article['title'] ?? '';
    $videoId  = $article['youtube_video_id'] ?? null;
    $embedUrl = $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
}}
<article style="max-width:780px;margin:0 auto">
    {{ if (!empty($article['category_name'])): }}
        <div style="margin-bottom:.75rem">
            <a href="/{{h $article['category_slug'] ?? '' }}" class="badge youtube">
                {{h $article['category_name'] }}
            </a>
        </div>
    {{ endif; }}

    <h1 style="font-size:2rem;font-weight:700;line-height:1.3;margin-bottom:1rem">
        {{h $article['title'] }}
    </h1>

    <div style="display:flex;gap:1rem;color:#64748b;font-size:.875rem;margin-bottom:1.5rem">
        {{ if (!empty($article['published_at'])): }}
            <span>{{h date('Y年m月d日', strtotime((string) $article['published_at'])) }}</span>
        {{ endif; }}
    </div>

    {{ if ($embedUrl): }}
        <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:.5rem;background:#000;margin-bottom:2rem">
            <iframe
                src="{{h $embedUrl }}"
                title="{{h $article['title'] }}"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                style="position:absolute;top:0;left:0;width:100%;height:100%"
            ></iframe>
        </div>
    {{ elseif (!empty($article['youtube_thumbnail'])): }}
        <a href="{{h $article['youtube_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" style="display:block;margin-bottom:2rem">
            <img src="{{h $article['youtube_thumbnail'] }}"
                 alt="{{h $article['title'] }}"
                 style="width:100%;border-radius:.5rem">
        </a>
    {{ endif; }}

    {{ if (!empty($article['youtube_url'])): }}
        <p style="margin-bottom:1.5rem">
            <a href="{{h $article['youtube_url'] }}" target="_blank" rel="noopener noreferrer"
               style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;background:#dc2626;color:#fff;border-radius:.375rem;font-weight:600">
                ▶ YouTubeで見る
            </a>
        </p>
    {{ endif; }}

    {{ if (!empty($article['content'])): }}
        <div style="font-size:1rem;line-height:1.8">
            {{= $article['content'] }}
        </div>
    {{ endif; }}

    <div style="margin-top:3rem;padding-top:1.5rem;border-top:1px solid #e2e8f0">
        <a href="/articles" style="color:#64748b;font-size:.875rem">&larr; 記事一覧に戻る</a>
    </div>

    {{= $this->render('components/related-articles') }}
    {{= $this->render('components/article-nav') }}
</article>
