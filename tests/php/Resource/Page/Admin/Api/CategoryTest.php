<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api;

use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Resource\Page\Admin\Api\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private CategoryInterface&MockObject $categoryService;
    private Category $resource;

    protected function setUp(): void
    {
        $this->categoryService = $this->createMock(CategoryInterface::class);
        $this->resource        = new Category($this->categoryService);
    }

    private function makeCategory(int $id = 1): array
    {
        return ['id' => $id, 'name' => 'Tech', 'slug' => 'tech', 'type' => 'normal', 'sort_order' => 0];
    }

    public function testOnGetReturns404WhenNotFound(): void
    {
        $this->categoryService->method('getById')->willReturn(null);

        $result = $this->resource->onGet(999);

        $this->assertSame(404, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnGetReturns200WithCategoryBody(): void
    {
        $category = $this->makeCategory();
        $this->categoryService->method('getById')->willReturn($category);

        $result = $this->resource->onGet(1);

        $this->assertSame(200, $result->code);
        $this->assertSame($category, $result->body);
    }

    public function testOnPutReturns404WhenNotFound(): void
    {
        $this->categoryService->method('getById')->willReturn(null);

        $result = $this->resource->onPut(999);

        $this->assertSame(404, $result->code);
    }

    public function testOnDeleteReturns204OnSuccess(): void
    {
        $this->categoryService->method('getById')->willReturn($this->makeCategory());
        $this->categoryService->method('delete')->willReturn(true);

        $result = $this->resource->onDelete(1);

        $this->assertSame(204, $result->code);
    }

    public function testOnDeleteReturns404WhenNotFound(): void
    {
        $this->categoryService->method('getById')->willReturn(null);

        $result = $this->resource->onDelete(999);

        $this->assertSame(404, $result->code);
    }
}
