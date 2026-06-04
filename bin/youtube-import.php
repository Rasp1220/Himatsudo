<?php
declare(strict_types=1);

/**
 * YouTube チャンネル動画バッチインポート
 *
 * 使い方:
 *   composer youtube-import
 *   composer youtube-import -- --dry-run
 *
 * .env に以下を設定してください:
 *   YOUTUBE_API_KEY=...
 *   YOUTUBE_CHANNEL_IDS=UCxxxxxxxx,UCyyyyyyyy
 *   YOUTUBE_IMPORT_MAX=20
 *   YOUTUBE_IMPORT_STATUS=draft
 *   YOUTUBE_IMPORT_AUTHOR_ID=1
 */

$root = dirname(__DIR__);

// Load .env
$envFile = $root . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// ─── helpers ────────────────────────────────────────────────────────────────

function cliOk(string $msg): void   { echo "\033[32m✓\033[0m {$msg}\n"; }
function cliInfo(string $msg): void { echo "  {$msg}\n"; }
function cliWarn(string $msg): void { echo "\033[33m⚠\033[0m {$msg}\n"; }
function cliFail(string $msg): void { fwrite(STDERR, "\033[31m✗\033[0m {$msg}\n"); }

function ytGet(string $url): ?array
{
    $ctx  = stream_context_create(['http' => ['timeout' => 15]]);
    $json = @file_get_contents($url, false, $ctx);
    if ($json === false) {
        return null;
    }
    $data = json_decode($json, true);
    if (isset($data['error'])) {
        cliFail('YouTube API エラー: ' . ($data['error']['message'] ?? 'unknown'));
        return null;
    }
    return $data ?: null;
}

function makeSlug(string $title, string $videoId): string
{
    $slug = mb_strtolower($title, 'UTF-8');
    $slug = (string) preg_replace('/[\s\-]+/', '-', $slug);
    $slug = (string) preg_replace('/[^a-z0-9\-]/', '', $slug);
    $slug = trim($slug, '-');
    $slug = substr($slug, 0, 60);
    return $slug !== '' ? $slug : 'yt-' . strtolower($videoId);
}

function uniqueSlug(PDO $pdo, string $base, string $videoId): string
{
    $stmt = $pdo->prepare('SELECT id FROM articles WHERE slug = ?');
    $stmt->execute([$base]);
    if (!$stmt->fetch()) {
        return $base;
    }
    return substr($base, 0, 48) . '-' . $videoId;
}

function isoToMysql(string $iso): string
{
    try {
        return (new DateTimeImmutable($iso))->format('Y-m-d H:i:s');
    } catch (Exception) {
        return (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }
}

/** チャンネルの動画を最大 $max 件取得して返す */
function fetchChannelVideos(string $channelId, int $max, string $apiKey): array
{
    $videos    = [];
    $pageToken = '';

    while (count($videos) < $max) {
        $perPage = min(50, $max - count($videos));
        $url     = 'https://www.googleapis.com/youtube/v3/search'
            . '?channelId=' . urlencode($channelId)
            . '&type=video&part=snippet'
            . '&maxResults=' . $perPage
            . '&order=date'
            . ($pageToken !== '' ? '&pageToken=' . urlencode($pageToken) : '')
            . '&key=' . urlencode($apiKey);

        $res = ytGet($url);
        if ($res === null || empty($res['items'])) {
            break;
        }

        foreach ($res['items'] as $item) {
            $videoId = $item['id']['videoId'] ?? null;
            if ($videoId === null) {
                continue;
            }
            $snippet  = $item['snippet'];
            $videos[] = [
                'video_id'     => (string) $videoId,
                'title'        => (string) ($snippet['title'] ?? ''),
                'description'  => (string) ($snippet['description'] ?? ''),
                'published_at' => (string) ($snippet['publishedAt'] ?? ''),
                'thumbnail'    => "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg",
            ];
        }

        $pageToken = $res['nextPageToken'] ?? '';
        if ($pageToken === '') {
            break;
        }
    }

    return $videos;
}

/** 1件の動画を記事としてDBに保存する。既存ならスキップ。戻り値: 'created'|'skipped'|'failed' */
function importVideo(PDO $pdo, array $v, int $categoryId = null, string $status = 'draft', int $authorId = 1): string
{
    $videoId = $v['video_id'];

    $stmt = $pdo->prepare('SELECT id FROM articles WHERE youtube_video_id = ?');
    $stmt->execute([$videoId]);
    if ($stmt->fetch()) {
        return 'skipped';
    }

    $title       = $v['title'] !== '' ? $v['title'] : "YouTube動画 {$videoId}";
    $publishedAt = $v['published_at'] !== '' ? isoToMysql($v['published_at']) : null;
    $excerpt     = $v['description'] !== '' ? mb_substr($v['description'], 0, 200, 'UTF-8') : null;

    if ($publishedAt === null && $status === 'published') {
        $publishedAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');
    }

    $slug = uniqueSlug($pdo, makeSlug($title, $videoId), $videoId);
    $data = [
        'title'             => $title,
        'slug'              => $slug,
        'content'           => '',
        'excerpt'           => $excerpt,
        'eye_catch_image'   => $v['thumbnail'],
        'author_id'         => $authorId,
        'status'            => $status,
        'youtube_url'       => "https://www.youtube.com/watch?v={$videoId}",
        'youtube_video_id'  => $videoId,
        'youtube_thumbnail' => $v['thumbnail'],
    ];
    if ($categoryId !== null) {
        $data['category_id'] = $categoryId;
    }
    if ($publishedAt !== null) {
        $data['published_at'] = $publishedAt;
    }

    try {
        $fields = array_keys($data);
        $pdo->prepare(
            'INSERT INTO articles (' . implode(', ', $fields) . ')'
            . ' VALUES (:' . implode(', :', $fields) . ')'
        )->execute($data);
        return 'created';
    } catch (PDOException $e) {
        cliFail("保存失敗 ({$title}): " . $e->getMessage());
        return 'failed';
    }
}

// ─── read config from .env ───────────────────────────────────────────────────

$apiKey = (string) ($_ENV['YOUTUBE_API_KEY'] ?? '');

$channelIds = array_values(array_filter(
    array_map('trim', explode(',', (string) ($_ENV['YOUTUBE_CHANNEL_IDS'] ?? '')))
));

$maxPerChannel = max(1, (int) ($_ENV['YOUTUBE_IMPORT_MAX']       ?? 20));
$status        = in_array($_ENV['YOUTUBE_IMPORT_STATUS'] ?? '', ['published', 'draft'], true)
    ? (string) $_ENV['YOUTUBE_IMPORT_STATUS']
    : 'draft';
$authorId      = max(1, (int) ($_ENV['YOUTUBE_IMPORT_AUTHOR_ID'] ?? 1));

// CLI override: --dry-run のみ受け付ける
$opts   = getopt('', ['dry-run', 'help']);
$dryRun = isset($opts['dry-run']);

// ─── help ────────────────────────────────────────────────────────────────────

if (isset($opts['help'])) {
    echo <<<HELP
YouTube チャンネル動画バッチインポート

使い方:
  composer youtube-import
  composer youtube-import -- --dry-run   # DBに保存せず確認のみ

.env 設定項目:
  YOUTUBE_API_KEY            YouTube Data API v3 キー (必須)
  YOUTUBE_CHANNEL_IDS        チャンネルID (カンマ区切りで複数可)
                             例: UCxxxxxxxx,UCyyyyyyyy
  YOUTUBE_IMPORT_MAX         1チャンネルあたり取得件数 (デフォルト: 20)
  YOUTUBE_IMPORT_STATUS      記事ステータス: draft|published (デフォルト: draft)
  YOUTUBE_IMPORT_AUTHOR_ID   投稿者ID (デフォルト: 1)

HELP;
    exit(0);
}

// ─── validate ────────────────────────────────────────────────────────────────

if ($apiKey === '') {
    cliFail('YOUTUBE_API_KEY が設定されていません。.env を確認してください。');
    exit(1);
}

if (empty($channelIds)) {
    cliFail('YOUTUBE_CHANNEL_IDS が設定されていません。.env を確認してください。');
    exit(1);
}

// ─── DB connect ──────────────────────────────────────────────────────────────

$pdo = null;
$categoryId = null;

if (!$dryRun) {
    $dsn    = (string) ($_ENV['DB_DSN']      ?? 'mysql:host=localhost;dbname=himatsudo;charset=utf8mb4');
    $dbUser = (string) ($_ENV['DB_USER']     ?? 'root');
    $dbPass = (string) ($_ENV['DB_PASSWORD'] ?? '');
    try {
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } catch (PDOException $e) {
        cliFail('DB接続失敗: ' . $e->getMessage());
        exit(1);
    }
    $row        = $pdo->query("SELECT id FROM categories WHERE type = 'youtube' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $categoryId = $row ? (int) $row['id'] : null;
}

// ─── run ─────────────────────────────────────────────────────────────────────

echo "\n\033[33m▶ YouTubeバッチインポート開始";
if ($dryRun) {
    echo ' [dry-run]';
}
echo "\033[0m\n";
cliInfo("チャンネル数: " . count($channelIds) . "  取得上限: {$maxPerChannel}件/ch  ステータス: {$status}");
echo "\n";

$totalCreated = 0;
$totalSkipped = 0;
$totalFailed  = 0;

foreach ($channelIds as $channelId) {
    echo "\033[36m── チャンネル: {$channelId}\033[0m\n";

    $videos = fetchChannelVideos($channelId, $maxPerChannel, $apiKey);

    if (empty($videos)) {
        cliWarn("動画が見つかりませんでした。\n");
        continue;
    }

    cliInfo(count($videos) . " 件取得");

    foreach ($videos as $v) {
        if ($dryRun) {
            $date = $v['published_at'] !== '' ? isoToMysql($v['published_at']) : '(未設定)';
            cliInfo("[dry-run] {$v['title']}");
            cliInfo("         ID: {$v['video_id']}  投稿日: {$date}");
            $totalCreated++;
            continue;
        }

        $result = importVideo($pdo, $v, $categoryId, $status, $authorId);
        match ($result) {
            'created' => (cliOk($v['title'])        and $totalCreated++),
            'skipped' => (cliWarn("スキップ(既存): {$v['title']}") and $totalSkipped++),
            'failed'  => $totalFailed++,
        };
    }

    echo "\n";
}

// ─── summary ─────────────────────────────────────────────────────────────────

if ($dryRun) {
    echo "\033[33m[dry-run] インポート予定: {$totalCreated} 件\033[0m\n\n";
} else {
    echo "\033[32m完了: 作成 {$totalCreated} 件 / スキップ(既存) {$totalSkipped} 件";
    if ($totalFailed > 0) {
        echo " / エラー {$totalFailed} 件";
    }
    echo "\033[0m\n\n";
}
