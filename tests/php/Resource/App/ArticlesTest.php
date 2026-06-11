<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\App;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Resource\App\Articles;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArticlesTest extends TestCase
{
    private ArticleInterface&MockObject $articleService;
    private CategoryInterface&MockObject $categoryService;
    private Articles $resource;

    protected function setUp(): void
    {
        $this->articleService  = $this->createMock(ArticleInterface::class);
        $this->categoryService = $this->createMock(CategoryInterface::class);
        $this->resource        = new Articles($this->articleService, $this->categoryService);
    }

    private function defaultListResult(): array
    {
        return ['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 12, 'last_page' => 1];
    }

    public function testOnGetWithoutCategoryIdSetsCorrectBody(): void
    {
        $this->articleService->method('getList')->willReturn($this->defaultListResult());
        $this->categoryService->method('getAll')->willReturn([]);

        $result = $this->resource->onGet(1, null, 12);

        $this->assertArrayHasKey('articles', $result->body);
        $this->assertArrayHasKey('total', $result->body);
        $this->assertArrayHasKey('page', $result->body);
        $this->assertArrayHasKey('per_page', $result->body);
        $this->assertArrayHasKey('last_page', $result->body);
        $this->assertArrayHasKey('categories', $result->body);
        $this->assertNull($result->body['category_id']);
        $this->assertNull($result->body['current_category']);
        $this->assertSame('記事一覧', $result->body['page_title']);
    }

    public function testOnGetWithCategoryIdSetsCategoryInfo(): void
    {
        $category = ['id' => 3, 'name' => 'Tech', 'slug' => 'tech'];
        $this->articleService->method('getList')->willReturn($this->defaultListResult());
        $this->categoryService->method('getAll')->willReturn([$category]);
        $this->categoryService->method('getById')->willReturn($category);

        $result = $this->resource->onGet(1, 3, 12);

        $this->assertSame(3, $result->body['category_id']);
        $this->assertSame($category, $result->body['current_category']);
        $this->assertSame('Tech', $result->body['page_title']);
    }
}
