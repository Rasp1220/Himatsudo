{{
    /**
     * @var array<string, mixed> $article
     * @var array<string, mixed>|null $prev
     * @var array<string, mixed>|null $next
     */
    $this->setLayout('layout');
    $this->page_title = $article['title'] ?? '';

    $blocks   = null;
    $richHtml = null;
    if (!empty($article['blocks'])) {
        $decoded = json_decode((string) $article['blocks'], true);
        if (is_array($decoded) && count($decoded) > 0) {
            $blocks = $decoded;
        } else {
            $richHtml = (string) $article['blocks'];
        }
    }
    if ($richHtml === null && !empty($article['content'])) {
        $richHtml = (string) $article['content'];
    }

    // 運営プロフィール経由かどうか。経由していれば「戻る先」を運営別一覧に合わせる。
    $authorContext = $author_context ?? null;

    $catType = $article['category_type'] ?? 'custom';
    if (!empty($authorContext)) {
        $listUrl   = '/author/' . (int) $authorContext['id'];
        $listLabel = (string) $authorContext['name'] . 'の記事';
    } else {
        [$listUrl, $listLabel] = match ($catType) {
            'blog'    => ['/blog',     'ブログ一覧'],
            'youtube' => ['/youtube',  'YouTube'],
            default   => ['/articles', '記事一覧'],
        };
    }
}}
<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        {{ if (!empty($authorContext)): }}
            <li class="breadcrumb-item"><a href="/staff">運営一覧</a></li>
            <li class="breadcrumb-item"><a href="{{h $listUrl }}">{{h $authorContext['name'] }}</a></li>
        {{ else: }}
            <li class="breadcrumb-item"><a href="{{h $listUrl }}">{{h $listLabel }}</a></li>
        {{ endif; }}
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">
            {{h mb_strimwidth((string) $article['title'], 0, 40, '…') }}
        </li>
    </ol>
</nav>

<article class="article-detail">
    {{ if (!empty($article['category_name'])): }}
        <div class="article-category">
            <a href="/{{h $article['category_slug'] ?? '' }}" class="badge {{h $article['category_type'] ?? '' }}">
                {{h $article['category_name'] }}
            </a>
        </div>
    {{ endif; }}

    <h1 class="article-title">{{h $article['title'] }}</h1>

    <div class="article-meta">
        {{ if (!empty($article['author_name'])): }}
            {{ if (!empty($article['author_id'])): }}
                <span>by <a href="/author/{{h $article['author_id'] }}" class="article-author-link">{{h $article['author_name'] }}</a></span>
            {{ else: }}
                <span>by {{h $article['author_name'] }}</span>
            {{ endif; }}
        {{ endif; }}
        {{ if (!empty($article['published_at'])): }}
            <span>{{h date('Y年m月d日', strtotime((string) $article['published_at'])) }}</span>
        {{ endif; }}
    </div>

    {{ if (!empty($article['eye_catch_image'])): }}
        <img src="{{h $article['eye_catch_image'] }}"
             alt="{{h $article['title'] }}"
             class="article-eyecatch">
    {{ endif; }}

    {{ if ($blocks !== null): }}
        <div class="article-blocks">
            {{ foreach ($blocks as $block): }}
                {{ $type = $block['type'] ?? ''; }}
                {{ if ($type === 'heading'): }}
                    {{ $level = max(2, min(4, (int) ($block['level'] ?? 2))); $tag = 'h' . $level; }}
                    <{{= $tag }} class="block-heading block-heading-{{= $level }}">
                        {{h $block['text'] ?? '' }}
                    </{{= $tag }}>

                {{ elseif ($type === 'text'): }}
                    <div class="article-body block-text">
                        {{= $block['html'] ?? '' }}
                    </div>

                {{ elseif ($type === 'image' && !empty($block['url'])): }}
                    <figure class="block-image">
                        <img src="{{h $block['url'] }}"
                             alt="{{h $block['alt'] ?? '' }}"
                             class="block-image-img">
                        {{ if (!empty($block['caption'])): }}
                            <figcaption class="block-image-caption">{{h $block['caption'] }}</figcaption>
                        {{ endif; }}
                    </figure>

                {{ elseif ($type === 'video' && !empty($block['video_id'])): }}
                    <figure class="block-video">
                        <div class="block-video-wrapper">
                            <iframe
                                src="https://www.youtube.com/embed/{{h $block['video_id'] }}"
                                title="{{h $block['caption'] ?? 'YouTube' }}"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                class="block-video-iframe">
                            </iframe>
                        </div>
                        {{ if (!empty($block['caption'])): }}
                            <figcaption class="block-video-caption">{{h $block['caption'] }}</figcaption>
                        {{ endif; }}
                    </figure>

                {{ endif; }}
            {{ endforeach; }}
        </div>

    {{ elseif ($richHtml !== null): }}
        <div class="article-body tinymce-content">
            {{= $richHtml }}
        </div>

    {{ else: }}
        <div class="article-body">
        </div>
    {{ endif; }}

    <div class="article-footer">
        <a href="{{h $listUrl }}">&larr; {{h $listLabel }}に戻る</a>
    </div>

    {{= $this->render('components/related-articles') }}
    {{= $this->render('components/article-nav') }}
</article>
