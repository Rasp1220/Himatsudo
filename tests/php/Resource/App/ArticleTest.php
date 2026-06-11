<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\App;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Resource\App\Article;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    private ArticleInterface&MockObject $articleService;
    private CategoryInterface&MockObject $categoryService;
    private Article $resource;

    protected function setUp(): void
    {
        $this->articleService  = $this->createMock(ArticleInterface::class);
        $this->categoryService = $this->createMock(CategoryInterface::class);
        $this->resource        = new Article($this->articleService, $this->categoryService);
    }

    public function testOnGetReturns404WhenArticleNotFound(): void
    {
        $this->articleService->method('getBySlug')->willReturn(null);

        $result = $this->resource->onGet('no-such-slug');

        $this->assertSame(404, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnGetReturns200WithBodyWhenArticleFound(): void
    {
        $article = [
            'id'            => 1,
            'title'         => 'Hello',
            'slug'          => 'hello',
            'category_type' => 'normal',
        ];
        $this->articleService->method('getBySlug')->willReturn($article);
        $this->categoryService->method('getAll')->willReturn([]);

        $result = $this->resource->onGet('hello');

        $this->assertSame(200, $result->code);
        $this->assertArrayHasKey('article', $result->body);
        $this->assertArrayHasKey('categories', $result->body);
        $this->assertArrayHasKey('page_title', $result->body);
    }
}
