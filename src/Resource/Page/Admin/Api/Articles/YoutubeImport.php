<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api\Articles;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Service\YoutubeService;
use RuntimeException;

class YoutubeImport extends ResourceObject
{
    public function __construct(private readonly YoutubeService $youtubeService)
    {
    }

    #[RequireAuth]
    public function onPost(string $url): static
    {
        try {
            $info = $this->youtubeService->fetchVideoInfo($url);
        } catch (RuntimeException $e) {
            $this->code = 422;
            $this->body = ['error' => $e->getMessage()];
            return $this;
        }

        $this->body = [
            'video_id'       => $info['video_id'],
            'title'          => $info['title'],
            'thumbnail'      => $info['thumbnail'],
            'youtube_url'    => "https://www.youtube.com/watch?v={$info['video_id']}",
            'embed_url'      => "https://www.youtube.com/embed/{$info['video_id']}",
            'description'    => $info['description'],
            'published_at'   => $info['published_at'],
        ];

        return $this;
    }
}
