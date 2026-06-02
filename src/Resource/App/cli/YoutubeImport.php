#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * YouTube チャンネル動画バッチインポート
 *
 * 使い方:
 *   php src/Resource/App/cli/YoutubeImport.php --channel=UCxxxxxxxx
 *   php src/Resource/App/cli/YoutubeImport.php --handle=@channelname --max=20 --status=published
 */

$root = dirname(__DIR__, 4);

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
    // Surface API errors
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
    return $slug !== '' ? $slug : 'yt-' . $videoId;
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

// ─── usage ──────────────────────────────────────────────────────────────────

$opts = getopt('', ['channel:', 'handle:', 'max:', 'status:', 'author-id:', 'dry-run', 'help']);

if (isset($opts['help']) || (empty($opts['channel']) && empty($opts['handle']))) {
    $script = basename(__FILE__);
    echo <<<HELP
YouTube チャンネル動画バッチインポート

使い方:
  php src/Resource/App/cli/{$script} [オプション]

オプション:
  --channel=<CHANNEL_ID>   チャンネルID (例: UCxxxxxxxxxxxxxxxxxxxxxxxx)
  --handle=<HANDLE>        チャンネルハンドル (例: @channelname)
  --max=<N>                取得する最大動画数 (デフォルト: 50)
  --status=<STATUS>        記事ステータス: draft または published (デフォルト: draft)
  --author-id=<ID>         投稿者のユーザーID (デフォルト: 1)
  --dry-run                DBに保存せず確認のみ
  --help                   このヘルプを表示

必要な環境変数 (.env に設定):
  YOUTUBE_API_KEY          YouTube Data API v3 キー

例:
  php src/Resource/App/cli/{$script} --channel=UCxxxxxxxx --max=20
  php src/Resource/App/cli/{$script} --handle=@channelname --status=published --dry-run

HELP;
    exit(isset($opts['help']) ? 0 : 1);
}

// ─── config ─────────────────────────────────────────────────────────────────

$apiKey   = (string) ($_ENV['YOUTUBE_API_KEY'] ?? '');
$maxItems = max(1, (int) ($opts['max'] ?? 50));
$status   = in_array($opts['status'] ?? '', ['published', 'draft'], true) ? (string) $opts['status'] : 'draft';
$authorId = max(1, (int) ($opts['author-id'] ?? 1));
$dryRun   = isset($opts['dry-run']);

if ($apiKey === '') {
    cliFail('YOUTUBE_API_KEY が設定されていません。.env を確認してください。');
    exit(1);
}

// ─── resolve channel ID ──────────────────────────────────────────────────────

$channelId = !empty($opts['channel']) ? (string) $opts['channel'] : null;

if ($channelId === null && !empty($opts['handle'])) {
    $handle = ltrim((string) $opts['handle'], '@');
    $url    = 'https://www.googleapis.com/youtube/v3/channels'
        . '?forHandle=' . urlencode($handle)
        . '&part=id'
        . '&key=' . urlencode($apiKey);
    $res       = ytGet($url);
    $channelId = $res['items'][0]['id'] ?? null;
    if ($channelId === null) {
        cliFail("チャンネルハンドル @{$handle} が見つかりませんでした。");
        exit(1);
    }
    cliInfo("チャンネルID解決: {$channelId}");
}

echo "\n\033[33m▶ チャンネル {$channelId} から最大 {$maxItems} 件を取得します";
if ($dryRun) {
    echo ' [dry-run]';
}
echo "\033[0m\n\n";

// ─── fetch videos ────────────────────────────────────────────────────────────

$videos    = [];
$pageToken = '';

while (count($videos) < $maxItems) {
    $perPage = min(50, $maxItems - count($videos));
    $url     = 'https://www.googleapis.com/youtube/v3/search'
        . '?channelId=' . urlencode((string) $channelId)
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

$total = count($videos);
echo "取得した動画: {$total} 件\n\n";

if ($total === 0) {
    cliWarn('動画が見つかりませんでした。チャンネルIDとAPIキーを確認してください。');
    exit(0);
}

// ─── DB connect ──────────────────────────────────────────────────────────────

$pdo        = null;
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
    if ($categoryId !== null) {
        cliInfo("YouTubeカテゴリID: {$categoryId}");
    } else {
        cliWarn('type=youtube のカテゴリが見つかりません。category_id は NULL で登録されます。');
    }
    echo "\n";
}

// ─── import ──────────────────────────────────────────────────────────────────

$created = 0;
$skipped = 0;
$failed  = 0;
$now     = (new DateTimeImmutable())->format('Y-m-d H:i:s');

foreach ($videos as $v) {
    $videoId     = $v['video_id'];
    $title       = $v['title'] !== '' ? $v['title'] : "YouTube動画 {$videoId}";
    $description = $v['description'];
    $publishedAt = $v['published_at'] !== '' ? isoToMysql($v['published_at']) : null;

    if ($dryRun) {
        $date = $publishedAt ?? '(未設定)';
        cliInfo("[dry-run] {$title}");
        cliInfo("         ID: {$videoId}  投稿日: {$date}");
        $created++;
        continue;
    }

    // Duplicate check
    $stmt = $pdo->prepare('SELECT id FROM articles WHERE youtube_video_id = ?');
    $stmt->execute([$videoId]);
    if ($stmt->fetch()) {
        cliWarn("スキップ (既存): {$title}");
        $skipped++;
        continue;
    }

    try {
        $slug    = uniqueSlug($pdo, makeSlug($title, $videoId), $videoId);
        $excerpt = $description !== '' ? mb_substr($description, 0, 200, 'UTF-8') : null;

        $resolvedPublishedAt = $publishedAt;
        if ($resolvedPublishedAt === null && $status === 'published') {
            $resolvedPublishedAt = $now;
        }

        $data = [
            'title'             => $title,
            'slug'              => $slug,
            'content'           => '',
            'excerpt'           => $excerpt,
            'eye_catch_image'   => '',
            'author_id'         => $authorId,
            'status'            => $status,
            'youtube_url'       => "https://www.youtube.com/watch?v={$videoId}",
            'youtube_video_id'  => $videoId,
            'youtube_thumbnail' => $v['thumbnail'],
        ];
        if ($categoryId !== null) {
            $data['category_id'] = $categoryId;
        }
        if ($resolvedPublishedAt !== null) {
            $data['published_at'] = $resolvedPublishedAt;
        }

        $fields = array_keys($data);
        $pdo->prepare(
            'INSERT INTO articles (' . implode(', ', $fields) . ')'
            . ' VALUES (:' . implode(', :', $fields) . ')'
        )->execute($data);

        cliOk($title);
        $created++;
    } catch (PDOException $e) {
        cliFail("保存失敗 ({$title}): " . $e->getMessage());
        $failed++;
    }
}

// ─── summary ─────────────────────────────────────────────────────────────────

echo "\n";
if ($dryRun) {
    echo "\033[33m[dry-run] インポート予定: {$created} 件\033[0m\n";
} else {
    echo "\033[32m完了: 作成 {$created} 件 / スキップ(既存) {$skipped} 件";
    if ($failed > 0) {
        echo " / エラー {$failed} 件";
    }
    echo "\033[0m\n";
}
