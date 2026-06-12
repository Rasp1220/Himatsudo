<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Resource\Page\Author;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorTest extends TestCase
{
    private UserInterface&MockObject $userService;
    private ArticleInterface&MockObject $articleService;
    private CategoryInterface&MockObject $categoryService;

    protected function setUp(): void
    {
        $this->userService     = $this->createMock(UserInterface::class);
        $this->articleService  = $this->createMock(ArticleInterface::class);
        $this->categoryService = $this->createMock(CategoryInterface::class);
    }

    private function makePage(): Author
    {
        return new Author($this->userService, $this->articleService, $this->categoryService);
    }

    public function testOnGetReturns404WhenAuthorNotFound(): void
    {
        $this->userService->method('getPublicById')->willReturn(null);

        $result = $this->makePage()->onGet(999);

        $this->assertSame(404, $result->code);
        $this->assertSame('error/404', $result->body['_template']);
    }

    public function testOnGetReturnsAuthorScopedArticleList(): void
    {
        $author = ['id' => 5, 'name' => 'Author', 'avatar' => null, 'bio' => 'hi'];
        $this->userService->method('getPublicById')->willReturn($author);
        $this->categoryService->method('getAll')->willReturn([]);

        $this->articleService->expects($this->once())
            ->method('getListByAuthor')
            ->with(5, 1, 12, 'published')
            ->willReturn(['items' => [], 'total' => 0, 'page' => 1, 'per_page' => 12, 'last_page' => 1]);

        $result = $this->makePage()->onGet(5);

        $this->assertSame(200, $result->code);
        $this->assertSame('author/index', $result->body['_template']);
        $this->assertSame($author, $result->body['author']);
        $this->assertSame('/author/5', $result->body['list_base_url']);
    }
}
