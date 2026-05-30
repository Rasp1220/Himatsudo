<?php
declare(strict_types=1);

namespace Himatsudo\Service;

use RuntimeException;

final class YoutubeService
{
    private const THUMBNAIL_BASE = 'https://img.youtube.com/vi/%s/maxresdefault.jpg';
    private const OEMBED_URL     = 'https://www.youtube.com/oembed?url=%s&format=json';

    /** @return array{video_id: string, title: string, thumbnail: string} */
    public function fetchVideoInfo(string $urlOrId): array
    {
        $videoId = $this->extractVideoId($urlOrId);

        if ($videoId === null) {
            throw new RuntimeException('Invalid YouTube URL or video ID');
        }

        $videoUrl = "https://www.youtube.com/watch?v={$videoId}";

        $oembedUrl = sprintf(self::OEMBED_URL, urlencode($videoUrl));
        $json      = @file_get_contents($oembedUrl);

        if ($json === false) {
            // Fallback: construct minimal info without oEmbed
            return [
                'video_id'  => $videoId,
                'title'     => '',
                'thumbnail' => sprintf(self::THUMBNAIL_BASE, $videoId),
            ];
        }

        $data = json_decode($json, true);
        return [
            'video_id'  => $videoId,
            'title'     => (string) ($data['title'] ?? ''),
            'thumbnail' => sprintf(self::THUMBNAIL_BASE, $videoId),
        ];
    }

    private function extractVideoId(string $input): ?string
    {
        // Plain video ID (11 characters)
        if (preg_match('/^[a-zA-Z0-9_\-]{11}$/', $input)) {
            return $input;
        }

        // youtu.be/VIDEO_ID
        if (preg_match('~youtu\.be/([a-zA-Z0-9_\-]{11})~', $input, $m)) {
            return $m[1];
        }

        // youtube.com/watch?v=VIDEO_ID
        if (preg_match('~[?&]v=([a-zA-Z0-9_\-]{11})~', $input, $m)) {
            return $m[1];
        }

        // youtube.com/embed/VIDEO_ID
        if (preg_match('~youtube\.com/embed/([a-zA-Z0-9_\-]{11})~', $input, $m)) {
            return $m[1];
        }

        return null;
    }
}
