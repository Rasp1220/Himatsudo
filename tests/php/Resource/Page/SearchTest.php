<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Resource\Page\Search;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * mirrors src/Resource/Page/Search.php
 */
class SearchTest extends TestCase
{
    private ArticleInterface&MockObject $articleService;
    private Search $resource;

    protected function setUp(): void
    {
        $this->articleService = $this->createMock(ArticleInterface::class);
        $this->resource       = new Search($this->articleService);
    }

    public function testOnGetWithEmptyQueryReturnsEmptyResultsWithoutCallingSearch(): void
    {
        $this->articleService->expects($this->never())->method('search');

        $result = $this->resource->onGet('');

        $this->assertSame(200, $result->code);
        $this->assertSame('', $result->body['q']);
        $this->assertSame([], $result->body['articles']);
        $this->assertSame(0, $result->body['total']);
        $this->assertSame('articles/search', $result->body['_template']);
    }

    public function testOnGetWithWhitespaceOnlyQueryTreatsAsEmpty(): void
    {
        $this->articleService->expects($this->never())->method('search');

        $result = $this->resource->onGet('   ');

        $this->assertSame('', $result->body['q']);
        $this->assertSame([], $result->body['articles']);
    }

    public function testOnGetWithKeywordCallsSearchAndReturnsResults(): void
    {
        $this->articleService
            ->method('search')
            ->willReturn([
                'items'     => [['id' => 1, 'title' => 'Test Article']],
                'total'     => 1,
                'page'      => 1,
                'last_page' => 1,
            ]);

        $result = $this->resource->onGet('test');

        $this->assertSame(200, $result->code);
        $this->assertSame('test', $result->body['q']);
        $this->assertCount(1, $result->body['articles']);
        $this->assertSame(1, $result->body['total']);
        $this->assertSame('サイト内検索', $result->body['page_title']);
        $this->assertSame('articles/search', $result->body['_template']);
    }

    public function testOnGetPassesPageParamToSearch(): void
    {
        $this->articleService
            ->expects($this->once())
            ->method('search')
            ->with('keyword', 3, 12)
            ->willReturn(['items' => [], 'total' => 0, 'page' => 3, 'last_page' => 1]);

        $this->resource->onGet('keyword', 3);
    }

    public function testOnGetNormalizesPageBelowOneToOne(): void
    {
        $this->articleService
            ->expects($this->once())
            ->method('search')
            ->with('keyword', 1, 12)
            ->willReturn(['items' => [], 'total' => 0, 'page' => 1, 'last_page' => 1]);

        $this->resource->onGet('keyword', -5);
    }
}
