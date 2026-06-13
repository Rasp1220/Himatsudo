<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\App;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Resource\App\Article;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    private ArticleInterface&MockObject $articleService;
    private CategoryInterface&MockObject $categoryService;
    private UserInterface&MockObject $userService;
    private Article $resource;

    protected function setUp(): void
    {
        $this->articleService  = $this->createMock(ArticleInterface::class);
        $this->categoryService = $this->createMock(CategoryInterface::class);
        $this->userService     = $this->createMock(UserInterface::class);
        $this->resource        = new Article($this->articleService, $this->categoryService, $this->userService);
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
        $this->assertNull($result->body['author_context']);
    }

    public function testOnGetUsesAuthorScopedPrevNextWhenAuthorIdGiven(): void
    {
        $article = [
            'id'            => 1,
            'title'         => 'Hello',
            'slug'          => 'hello',
            'category_type' => 'blog',
            'published_at'  => '2024-01-01 00:00:00',
        ];
        $author = ['id' => 5, 'name' => 'Author', 'avatar' => null, 'bio' => null];

        $this->articleService->method('getBySlug')->willReturn($article);
        $this->userService->method('getPublicById')->willReturn($author);
        $this->categoryService->method('getAll')->willReturn([]);

        // 運営別の前後記事メソッドが使われ、通常版は呼ばれないこと
        $this->articleService->expects($this->once())
            ->method('getPrevNextByAuthor')
            ->with(1, '2024-01-01 00:00:00', 5)
            ->willReturn(['prev' => null, 'next' => null]);
        $this->articleService->expects($this->never())->method('getPrevNext');

        $result = $this->resource->onGet('hello', 5);

        $this->assertSame(200, $result->code);
        $this->assertSame($author, $result->body['author_context']);
    }
}
