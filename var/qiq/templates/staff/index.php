{{
    /**
     * @var array<int, array<string, mixed>> $staff
     * @var string $page_title
     */
    $this->setLayout('layout');
    $this->page_title = $page_title ?? '運営一覧';
}}
<nav class="breadcrumb" aria-label="パンくずリスト">
    <ol class="breadcrumb-list">
        <li class="breadcrumb-item"><a href="/">ホーム</a></li>
        <li class="breadcrumb-item breadcrumb-current" aria-current="page">運営一覧</li>
    </ol>
</nav>

<h1 class="page-title">運営一覧</h1>

{{ if (!empty($staff)): }}
    <div class="staff-grid">
        {{ foreach ($staff as $member): }}
            <a href="/author/{{h $member['id'] }}" class="staff-card">
                <div class="staff-avatar">
                    {{ if (!empty($member['avatar'])): }}
                        <img src="{{h $member['avatar'] }}" alt="{{h $member['name'] }}" loading="lazy">
                    {{ else: }}
                        <span class="staff-avatar-fallback">{{h mb_substr((string) $member['name'], 0, 1) }}</span>
                    {{ endif; }}
                </div>
                <div class="staff-name">{{h $member['name'] }}</div>
            </a>
        {{ endforeach; }}
    </div>
{{ else: }}
    <div class="no-articles-msg">運営が登録されていません。</div>
{{ endif; }}
