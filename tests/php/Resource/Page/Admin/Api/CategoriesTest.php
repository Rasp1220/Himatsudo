<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api;

use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Resource\Page\Admin\Api\Categories;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoriesTest extends TestCase
{
    private CategoryInterface&MockObject $categoryService;
    private Categories $resource;

    protected function setUp(): void
    {
        $this->categoryService = $this->createMock(CategoryInterface::class);
        $this->resource        = new Categories($this->categoryService);
    }

    public function testOnGetReturnsCategoryList(): void
    {
        $categories = [
            ['id' => 1, 'name' => 'Tech', 'slug' => 'tech', 'type' => 'normal'],
            ['id' => 2, 'name' => 'Blog', 'slug' => 'blog', 'type' => 'blog'],
        ];
        $this->categoryService->method('getAll')->willReturn($categories);

        $result = $this->resource->onGet();

        $this->assertSame(200, $result->code);
        $this->assertSame($categories, $result->body);
    }

    public function testOnPostReturns422ForInvalidType(): void
    {
        $result = $this->resource->onPost('My Cat', 'my-cat', 'invalid', 0);

        $this->assertSame(422, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnPostReturns201ForValidTypeNormal(): void
    {
        $created = ['id' => 1, 'name' => 'News', 'slug' => 'news', 'type' => 'normal'];
        $this->categoryService->method('create')->willReturn($created);

        $result = $this->resource->onPost('News', 'news', 'normal', 0);

        $this->assertSame(201, $result->code);
        $this->assertSame($created, $result->body);
    }

    public function testOnPostReturns201ForValidTypeBlog(): void
    {
        $created = ['id' => 2, 'name' => 'Blog', 'slug' => 'blog', 'type' => 'blog'];
        $this->categoryService->method('create')->willReturn($created);

        $result = $this->resource->onPost('Blog', 'blog', 'blog', 0);

        $this->assertSame(201, $result->code);
    }

    public function testOnPostReturns201ForValidTypeYoutube(): void
    {
        $created = ['id' => 3, 'name' => 'Videos', 'slug' => 'videos', 'type' => 'youtube'];
        $this->categoryService->method('create')->willReturn($created);

        $result = $this->resource->onPost('Videos', 'videos', 'youtube', 0);

        $this->assertSame(201, $result->code);
    }

    public function testOnPostReturns201ForValidTypeCustom(): void
    {
        $created = ['id' => 4, 'name' => 'Custom', 'slug' => 'custom', 'type' => 'custom'];
        $this->categoryService->method('create')->willReturn($created);

        $result = $this->resource->onPost('Custom', 'custom', 'custom', 0);

        $this->assertSame(201, $result->code);
    }
}
