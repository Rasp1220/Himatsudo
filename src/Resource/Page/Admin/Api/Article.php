<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Repository\ArticleRepository;

class Article extends ResourceObject
{
    public function __construct(private readonly ArticleRepository $articleRepository)
    {
    }

    public function onGet(int $id): static
    {
        $article = $this->articleRepository->findById($id);
        if ($article === null) {
            $this->code = 404;
            $this->body = ['error' => 'Article not found'];
            return $this;
        }
        $this->body = $article;
        return $this;
    }

    #[RequireAuth]
    public function onPut(
        int     $id,
        ?string $title         = null,
        ?string $slug          = null,
        ?string $status        = null,
        ?string $content       = null,
        ?string $blocks        = null,
        ?string $excerpt       = null,
        ?string $eye_catch_image = null,
        ?int    $category_id   = null,
        ?string $youtube_url   = null,
        ?string $youtube_video_id  = null,
        ?string $youtube_thumbnail = null,
        ?string $published_at  = null
    ): static {
        if ($this->articleRepository->findById($id) === null) {
            $this->code = 404;
            $this->body = ['error' => 'Article not found'];
            return $this;
        }
        $data = array_filter(compact(
            'title', 'slug', 'status', 'content', 'blocks', 'excerpt', 'eye_catch_image',
            'youtube_url', 'youtube_video_id', 'youtube_thumbnail', 'published_at'
        ), fn($v) => $v !== null && $v !== '');
        // category_id=0 means "explicitly clear"; any other non-null int sets the category
        if ($category_id !== null) {
            $data['category_id'] = $category_id === 0 ? null : $category_id;
        }
        $this->articleRepository->update($id, $data);
        $this->body = $this->articleRepository->findById($id);
        return $this;
    }

    #[RequireAuth]
    public function onDelete(int $id): static
    {
        if ($this->articleRepository->findById($id) === null) {
            $this->code = 404;
            $this->body = ['error' => 'Article not found'];
            return $this;
        }
        $this->articleRepository->delete($id);
        $this->code = 204;
        $this->body = null;
        return $this;
    }
}
