<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Resource\Page\Admin\Api\Article;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    private ArticleInterface&MockObject $articleService;
    private Article $resource;

    protected function setUp(): void
    {
        $this->articleService = $this->createMock(ArticleInterface::class);
        $this->resource       = new Article($this->articleService);
    }

    private function makeArticle(int $id = 1): array
    {
        return [
            'id'         => $id,
            'title'      => 'Test Article',
            'slug'       => 'test-article',
            'status'     => 'published',
            'author_id'  => 1,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];
    }

    public function testOnGetReturns404WhenNotFound(): void
    {
        $this->articleService->method('getById')->willReturn(null);

        $result = $this->resource->onGet(999);

        $this->assertSame(404, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnGetReturnsArticleBodyWhenFound(): void
    {
        $article = $this->makeArticle(1);
        $this->articleService->method('getById')->willReturn($article);

        $result = $this->resource->onGet(1);

        $this->assertSame(200, $result->code);
        $this->assertSame($article, $result->body);
    }

    public function testOnPutReturns404WhenNotFound(): void
    {
        $this->articleService->method('getById')->willReturn(null);

        $result = $this->resource->onPut(999);

        $this->assertSame(404, $result->code);
    }

    public function testOnPutReturns422WhenCategoryIdIsZero(): void
    {
        $this->articleService->method('getById')->willReturn($this->makeArticle());

        $result = $this->resource->onPut(1, null, null, null, null, null, null, null, 0);

        $this->assertSame(422, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnDeleteReturns204WhenDeleted(): void
    {
        $this->articleService->method('getById')->willReturn($this->makeArticle());
        $this->articleService->method('delete')->willReturn(true);

        $result = $this->resource->onDelete(1);

        $this->assertSame(204, $result->code);
    }

    public function testOnDeleteReturns404WhenNotFound(): void
    {
        $this->articleService->method('getById')->willReturn(null);

        $result = $this->resource->onDelete(999);

        $this->assertSame(404, $result->code);
    }
}
