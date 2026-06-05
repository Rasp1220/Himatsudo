<?php

declare(strict_types=1);

namespace Himatsudo\Service;

use RuntimeException;

final class YoutubeService
{
    private const THUMBNAIL_BASE = 'https://img.youtube.com/vi/%s/maxresdefault.jpg';
    private const OEMBED_URL     = 'https://www.youtube.com/oembed?url=%s&format=json';
    private const DATA_API_URL   = 'https://www.googleapis.com/youtube/v3/videos?id=%s&part=snippet&key=%s';

    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = (string) ($_ENV['YOUTUBE_API_KEY'] ?? '');
    }

    /**
     * @return array{video_id: string, title: string, thumbnail: string, description: string, published_at: string}
     */
    public function fetchVideoInfo(string $urlOrId): array
    {
        $videoId = $this->extractVideoId($urlOrId);

        if ($videoId === null) {
            throw new RuntimeException('Invalid YouTube URL or video ID');
        }

        if ($this->apiKey !== '') {
            return $this->fetchViaDataApi($videoId);
        }

        return $this->fetchViaOembed($videoId);
    }

    /** @return array{video_id: string, title: string, thumbnail: string, description: string, published_at: string} */
    private function fetchViaDataApi(string $videoId): array
    {
        $url  = sprintf(self::DATA_API_URL, urlencode($videoId), urlencode($this->apiKey));
        $json = @file_get_contents($url);

        if ($json === false) {
            return $this->fetchViaOembed($videoId);
        }

        $data = json_decode($json, true);
        $item = $data['items'][0] ?? null;

        if ($item === null) {
            return $this->fetchViaOembed($videoId);
        }

        $snippet    = $item['snippet'];
        $thumbnails = $snippet['thumbnails'] ?? [];
        $thumbnail  = $thumbnails['maxres']['url']
            ?? $thumbnails['high']['url']
            ?? $thumbnails['medium']['url']
            ?? sprintf(self::THUMBNAIL_BASE, $videoId);

        return [
            'video_id'     => $videoId,
            'title'        => (string) ($snippet['title'] ?? ''),
            'thumbnail'    => (string) $thumbnail,
            'description'  => (string) ($snippet['description'] ?? ''),
            'published_at' => (string) ($snippet['publishedAt'] ?? ''),
        ];
    }

    /** @return array{video_id: string, title: string, thumbnail: string, description: string, published_at: string} */
    private function fetchViaOembed(string $videoId): array
    {
        $videoUrl  = "https://www.youtube.com/watch?v={$videoId}";
        $oembedUrl = sprintf(self::OEMBED_URL, urlencode($videoUrl));
        $json      = @file_get_contents($oembedUrl);

        if ($json === false) {
            return [
                'video_id'     => $videoId,
                'title'        => '',
                'thumbnail'    => sprintf(self::THUMBNAIL_BASE, $videoId),
                'description'  => '',
                'published_at' => '',
            ];
        }

        $data = json_decode($json, true);
        return [
            'video_id'     => $videoId,
            'title'        => (string) ($data['title'] ?? ''),
            'thumbnail'    => sprintf(self::THUMBNAIL_BASE, $videoId),
            'description'  => '',
            'published_at' => '',
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
