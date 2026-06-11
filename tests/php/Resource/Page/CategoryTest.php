<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Resource\Page\Category;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    use ResourceStubTrait;

    private CategoryInterface&MockObject $categoryService;

    protected function setUp(): void
    {
        $this->categoryService = $this->createMock(CategoryInterface::class);
    }

    public function testOnGetReturns404WhenCategoryNotFoundBySlug(): void
    {
        $this->categoryService->method('getBySlug')->willReturn(null);
        $resource = $this->makeResourceStub(200, []);
        $page     = new Category($resource, $this->categoryService);

        $result = $page->onGet('no-such-slug');

        $this->assertSame(404, $result->code);
    }

    public function testOnGetReturnsBodyWithTemplateAndCategorySlug(): void
    {
        $category = ['id' => 3, 'name' => 'Tech', 'slug' => 'tech'];
        $this->categoryService->method('getBySlug')->willReturn($category);

        $innerBody = ['articles' => [], 'total' => 0, 'page' => 1, 'per_page' => 12, 'last_page' => 1, 'categories' => []];
        $resource  = $this->makeResourceStub(200, $innerBody);
        $page      = new Category($resource, $this->categoryService);

        $result = $page->onGet('tech');

        $this->assertSame(200, $result->code);
        $this->assertSame('articles/index', $result->body['_template']);
        $this->assertSame('tech', $result->body['category_slug']);
    }
}
