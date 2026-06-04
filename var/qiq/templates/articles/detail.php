{{
    /** @var array<string, mixed> $article */
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

    $catType = $article['category_type'] ?? 'custom';
    [$listUrl, $listLabel] = match ($catType) {
        'blog'    => ['/blog',     'ブログ一覧'],
        'youtube' => ['/youtube',  'YouTube'],
        default   => ['/articles', '記事一覧'],
    };
}}
<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        <li class="breadcrumb-item"><a href="{{= $listUrl }}">{{= $listLabel }}</a></li>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">
            {{= mb_strimwidth((string) $article['title'], 0, 40, '…') }}
        </li>
    </ol>
</nav>

<article class="article-detail">
    {{ if (!empty($article['category_name'])): }}
    <div class="article-category">
        <a href="/{{= $article['category_slug'] ?? '' }}" class="badge {{= $article['category_type'] ?? '' }}">
            {{= $article['category_name'] }}
        </a>
    </div>
    {{ endif; }}

    <h1 class="article-title">{{= $article['title'] }}</h1>

    <div class="article-meta">
        {{ if (!empty($article['author_name'])): }}
        <span>by {{= $article['author_name'] }}</span>
        {{ endif; }}
        {{ if (!empty($article['published_at'])): }}
        <span>{{= date('Y年m月d日', strtotime((string) $article['published_at'])) }}</span>
        {{ endif; }}
    </div>

    {{ if (!empty($article['eye_catch_image'])): }}
    <img src="{{= $article['eye_catch_image'] }}"
         alt="{{= $article['title'] }}"
         class="article-eyecatch">
    {{ endif; }}

    {{ if ($blocks !== null): }}
    <div class="article-blocks">
        {{ foreach ($blocks as $block): }}
        {{ $type = $block['type'] ?? ''; }}
        {{ if ($type === 'heading'): }}
        {{ $level = max(2, min(4, (int) ($block['level'] ?? 2))); $tag = 'h' . $level; }}
        <{{= $tag }} class="block-heading block-heading-{{= $level }}">
            {{= $block['text'] ?? '' }}
        </{{= $tag }}>

        {{ elseif ($type === 'text'): }}
        <div class="article-body block-text">
            {{~ $block['html'] ?? '' }}
        </div>

        {{ elseif ($type === 'image' && !empty($block['url'])): }}
        <figure class="block-image">
            <img src="{{= $block['url'] }}"
                 alt="{{= $block['alt'] ?? '' }}"
                 class="block-image-img">
            {{ if (!empty($block['caption'])): }}
            <figcaption class="block-image-caption">{{= $block['caption'] }}</figcaption>
            {{ endif; }}
        </figure>

        {{ elseif ($type === 'video' && !empty($block['video_id'])): }}
        <figure class="block-video">
            <div class="block-video-wrapper">
                <iframe
                    src="https://www.youtube.com/embed/{{= $block['video_id'] }}"
                    title="{{= $block['caption'] ?? 'YouTube' }}"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    class="block-video-iframe">
                </iframe>
            </div>
            {{ if (!empty($block['caption'])): }}
            <figcaption class="block-video-caption">{{= $block['caption'] }}</figcaption>
            {{ endif; }}
        </figure>

        {{ endif; }}
        {{ endforeach; }}
    </div>

    {{ elseif ($richHtml !== null): }}
    <div class="article-body tinymce-content">
        {{~ $richHtml }}
    </div>

    {{ else: }}
    <div class="article-body">
    </div>
    {{ endif; }}

    <div class="article-footer">
        <a href="{{= $listUrl }}">&larr; {{= $listLabel }}に戻る</a>
    </div>
</article>
