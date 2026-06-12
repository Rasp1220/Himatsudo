<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Resource\Page\Admin\Api\Articles;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArticlesTest extends TestCase
{
    private ArticleInterface&MockObject $articleService;
    private Articles $resource;

    protected function setUp(): void
    {
        $this->articleService = $this->createMock(ArticleInterface::class);
        $this->resource       = new Articles($this->articleService);
    }

    public function testOnGetReturnsPaginatedList(): void
    {
        $list = ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 20, 'last_page' => 1];
        $this->articleService->method('getAdminList')->willReturn($list);

        $result = $this->resource->onGet();

        $this->assertSame(200, $result->code);
        $this->assertSame($list, $result->body);
    }

    public function testOnPostReturns422WhenCategoryIdIsNull(): void
    {
        $result = $this->resource->onPost('Title', 'title', 1, 'draft', null, null, null, null, null);

        $this->assertSame(422, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnPostReturns422WhenCategoryIdIsZero(): void
    {
        $result = $this->resource->onPost('Title', 'title', 1, 'draft', null, null, null, null, 0);

        $this->assertSame(422, $result->code);
    }

    public function testOnPostReturns201WithCreatedArticleOnSuccess(): void
    {
        $created = ['id' => 1, 'title' => 'Title', 'slug' => 'title', 'category_id' => 2];
        $this->articleService->method('create')->willReturn($created);

        $result = $this->resource->onPost('Title', 'title', 1, 'draft', null, null, null, null, 2);

        $this->assertSame(201, $result->code);
        $this->assertSame($created, $result->body);
    }
}
