<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Resource\Page\Blog;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BlogTest extends TestCase
{
    private ArticleInterface&MockObject $articleService;
    private CategoryInterface&MockObject $categoryService;
    private Blog $resource;

    protected function setUp(): void
    {
        $this->articleService  = $this->createMock(ArticleInterface::class);
        $this->categoryService = $this->createMock(CategoryInterface::class);
        $this->resource        = new Blog($this->articleService, $this->categoryService);
    }

    public function testOnGetWhenNoBlogCategoryExistsReturnsEmptyArticlesListAndDefaultTitle(): void
    {
        $this->categoryService->method('getAll')->willReturn([]);
        $this->categoryService->method('getByType')->willReturn(null);

        $result = $this->resource->onGet();

        $this->assertSame(200, $result->code);
        $this->assertSame([], $result->body['articles']);
        $this->assertSame('ブログ', $result->body['page_title']);
        $this->assertNull($result->body['category_id']);
    }

    public function testOnGetWhenBlogCategoryExistsCallsGetListWithCategoryId(): void
    {
        $blogCategory = ['id' => 2, 'name' => 'My Blog', 'slug' => 'blog', 'type' => 'blog'];
        $articles     = [['id' => 1, 'title' => 'Post 1']];

        $this->categoryService->method('getAll')->willReturn([$blogCategory]);
        $this->categoryService->method('getByType')->with('blog')->willReturn($blogCategory);
        $this->articleService->method('getList')
            ->with(1, 12, 2, 'published')
            ->willReturn(['items' => $articles, 'total' => 1, 'page' => 1, 'per_page' => 12, 'last_page' => 1]);

        $result = $this->resource->onGet(1, 12);

        $this->assertSame($articles, $result->body['articles']);
        $this->assertSame('My Blog', $result->body['page_title']);
        $this->assertSame(2, $result->body['category_id']);
    }
}
