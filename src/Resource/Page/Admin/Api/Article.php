<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Interfaces\ArticleInterface as ArticleServiceInterface;

class Article extends ResourceObject
{
    public function __construct(private readonly ArticleServiceInterface $articleService)
    {
    }

    public function onGet(int $id): static
    {
        $article = $this->articleService->getById($id);
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
        ?string $title                = null,
        ?string $slug                 = null,
        ?string $status               = null,
        ?string $content              = null,
        ?string $blocks               = null,
        ?string $excerpt              = null,
        ?string $eye_catch_image      = null,
        ?int    $category_id          = null,
        ?string $youtube_url          = null,
        ?string $youtube_video_id     = null,
        ?string $youtube_thumbnail    = null,
        ?string $published_at         = null,
        ?string $related_article_ids  = null
    ): static {
        if ($this->articleService->getById($id) === null) {
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
        // related_article_ids: JSON string "[1,2,3]" sets IDs; "[]" or "" clears
        if ($related_article_ids !== null) {
            $decoded = json_decode($related_article_ids, true);
            $data['related_article_ids'] = (is_array($decoded) && count($decoded) > 0) ? $related_article_ids : null;
        }
        $this->body = $this->articleService->update($id, $data);
        return $this;
    }

    #[RequireAuth]
    public function onDelete(int $id): static
    {
        if ($this->articleService->getById($id) === null) {
            $this->code = 404;
            $this->body = ['error' => 'Article not found'];
            return $this;
        }
        $this->articleService->delete($id);
        $this->code = 204;
        $this->body = null;
        return $this;
    }
}
