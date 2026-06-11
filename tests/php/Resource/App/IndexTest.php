<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\App;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Resource\App\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    private ArticleInterface&MockObject $articleService;
    private CategoryInterface&MockObject $categoryService;
    private Index $resource;

    protected function setUp(): void
    {
        $this->articleService  = $this->createMock(ArticleInterface::class);
        $this->categoryService = $this->createMock(CategoryInterface::class);
        $this->resource        = new Index($this->articleService, $this->categoryService);
    }

    public function testOnGetReturnsBodyWithRequiredKeys(): void
    {
        $this->categoryService->method('getAll')->willReturn([]);
        $this->articleService->method('getLatest')->willReturn([]);
        $this->articleService->method('getLatestByCategory')->willReturn([]);

        $result = $this->resource->onGet();

        $this->assertArrayHasKey('latest_articles', $result->body);
        $this->assertArrayHasKey('categories_with_articles', $result->body);
        $this->assertArrayHasKey('categories', $result->body);
        $this->assertSame('ホーム', $result->body['page_title']);
    }

    public function testOnGetExcludesCategoriesWithNoArticles(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Cat A'],
            ['id' => 2, 'name' => 'Cat B'],
        ];

        $this->categoryService->method('getAll')->willReturn($categories);
        $this->articleService->method('getLatest')->willReturn([]);
        $this->articleService->method('getLatestByCategory')
            ->willReturnCallback(function (int $categoryId) {
                // Only category 1 has articles
                return $categoryId === 1 ? [['id' => 10, 'title' => 'Article']] : [];
            });

        $result = $this->resource->onGet();

        $this->assertCount(1, $result->body['categories_with_articles']);
        $this->assertSame(1, $result->body['categories_with_articles'][0]['category']['id']);
    }
}
