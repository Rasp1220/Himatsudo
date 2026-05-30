<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Repository\ArticleRepository;

class Articles extends ResourceObject
{
    public function __construct(private readonly ArticleRepository $articleRepository)
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
        $result = $this->articleRepository->findAllAdmin($page, $per_page, $category_id, $status, $keyword);
        $this->body = [
            'items'     => $result['items'],
            'total'     => $result['total'],
            'page'      => $page,
            'per_page'  => $per_page,
            'last_page' => (int) ceil($result['total'] / max(1, $per_page)),
        ];
        return $this;
    }

    #[RequireAuth]
    public function onPost(
        string  $title,
        string  $slug,
        int     $author_id,
        string  $status            = 'draft',
        ?string $content           = null,
        ?string $excerpt           = null,
        ?string $eye_catch_image   = null,
        ?int    $category_id       = null,
        ?string $youtube_url       = null,
        ?string $youtube_video_id  = null,
        ?string $youtube_thumbnail = null
    ): static {
        $data = array_filter(compact(
            'title', 'slug', 'author_id', 'status', 'content', 'excerpt',
            'eye_catch_image', 'category_id', 'youtube_url', 'youtube_video_id', 'youtube_thumbnail'
        ), fn($v) => $v !== null);
        $id = $this->articleRepository->create($data);
        $this->code = 201;
        $this->body = $this->articleRepository->findById($id);
        return $this;
    }
}
