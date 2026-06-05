<?php
/**
 * @var array<int, array<string, mixed>> $items
 * @var int    $total
 * @var int    $page
 * @var int    $last_page
 * @var string $keyword
 */
$this->setLayout('layout');
$this->page_title = $keyword !== '' ? "「{$keyword}」の検索結果" : 'サイト内検索';
?>

<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">検索</li>
    </ol>
</nav>

<div class="search-page">
    <h1 class="search-heading">サイト内検索</h1>

    <form class="search-form" action="/search" method="get" role="search">
        <input
            type="search"
            name="q"
            value="{{ $keyword }}"
            placeholder="キーワードを入力"
            class="search-input"
            autofocus
        >
        <button type="submit" class="search-submit">検索</button>
    </form>

    {% if ($keyword !== '') %}
    <p class="search-result-count">
        {% if ($total > 0) %}
            「{{ $keyword }}」の検索結果: {{ (string) $total }} 件
        {% else %}
            「{{ $keyword }}」に一致する記事は見つかりませんでした。
        {% endif %}
    </p>
    {% endif %}

    {% if (!empty($items)) %}
    <ul class="search-results">
        {% foreach ($items as $item) %}
        {% $thumb   = $item['eye_catch_image'] ?? $item['youtube_thumbnail'] ?? '' %}
        {% $catType = $item['category_type'] ?? 'custom' %}
        {% $url     = '/articles/' . rawurlencode((string) ($item['slug'] ?? '')) %}
        <li class="search-result-item">
            <a href="{{ $url }}" class="search-result-link">
                <div class="search-result-thumbnail">
                    {% if ($thumb) %}
                    <img src="{{ $thumb }}"
                         alt="{{ $item['title'] ?? '' }}"
                         class="search-result-img"
                         loading="lazy">
                    {% else %}
                    <div class="search-result-no-img">NO IMAGE</div>
                    {% endif %}
                </div>
                <div class="search-result-body">
                    <p class="search-result-title">{{ $item['title'] ?? '' }}</p>
                    {% if (!empty($item['published_at'])) %}
                    <time class="search-result-date" datetime="{{ $item['published_at'] }}">
                        {{ date('Y年m月d日', strtotime((string) $item['published_at'])) }}
                    </time>
                    {% endif %}
                    {% if (!empty($item['category_name'])) %}
                    <span class="search-result-category badge {{ $catType }}">
                        {{ $item['category_name'] }}
                    </span>
                    {% endif %}
                </div>
            </a>
        </li>
        {% endforeach %}
    </ul>

    {% if (($last_page ?? 1) > 1) %}
    <nav class="pagination" aria-label="検索結果のページネーション">
        {% if ($page > 1) %}
        <a href="/search?q={{ urlencode($keyword) }}&page={{ $page - 1 }}">&laquo;</a>
        {% endif %}
        {% for ($p = max(1, $page - 2); $p <= min($last_page, $page + 2); $p++) %}
        {% if ($p === $page) %}
        <span class="current">{{ $p }}</span>
        {% else %}
        <a href="/search?q={{ urlencode($keyword) }}&page={{ $p }}">{{ $p }}</a>
        {% endif %}
        {% endfor %}
        {% if ($page < $last_page) %}
        <a href="/search?q={{ urlencode($keyword) }}&page={{ $page + 1 }}">&raquo;</a>
        {% endif %}
    </nav>
    {% endif %}

    {% endif %}
</div>
