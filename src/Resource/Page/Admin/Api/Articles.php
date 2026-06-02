<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Interfaces\ArticleInterface as ArticleServiceInterface;

class Articles extends ResourceObject
{
    public function __construct(private readonly ArticleServiceInterface $articleService)
    {
    }

    #[RequireAuth]
    public function onGet(
        int     $page        = 1,
        int     $per_page    = 20,
        ?int    $category_id = null,
        ?string $status      = null,
        ?string $keyword     = null
    ): static {
        $this->body = $this->articleService->getAdminList($page, $per_page, $category_id, $status, $keyword);
        return $this;
    }

    #[RequireAuth]
    public function onPost(
        string  $title,
        string  $slug,
        int     $author_id,
        string  $status            = 'draft',
        ?string $content           = null,
        ?string $blocks            = null,
        ?string $excerpt           = null,
        ?string $eye_catch_image   = null,
        ?int    $category_id       = null,
        ?string $youtube_url       = null,
        ?string $youtube_video_id  = null,
        ?string $youtube_thumbnail = null,
        ?string $published_at      = null
    ): static {
        $data = array_filter(compact(
            'title', 'slug', 'author_id', 'status', 'content', 'blocks', 'excerpt',
            'eye_catch_image', 'youtube_url', 'youtube_video_id', 'youtube_thumbnail',
            'published_at'
        ), fn($v) => $v !== null && $v !== '');
        // category_id=0 means "no category" (CMS sends 0 when unselected)
        if ($category_id !== null) {
            $data['category_id'] = $category_id === 0 ? null : $category_id;
        }
        $this->code = 201;
        $this->body = $this->articleService->create($data);
        return $this;
    }
}
