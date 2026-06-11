<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Resource\Page\Youtube;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class YoutubeTest extends TestCase
{
    private ArticleInterface&MockObject $articleService;
    private CategoryInterface&MockObject $categoryService;
    private Youtube $resource;

    protected function setUp(): void
    {
        $this->articleService  = $this->createMock(ArticleInterface::class);
        $this->categoryService = $this->createMock(CategoryInterface::class);
        $this->resource        = new Youtube($this->articleService, $this->categoryService);
    }

    public function testOnGetWhenNoYoutubeCategoryExistsReturnsEmptyArticlesAndDefaultTitle(): void
    {
        $this->categoryService->method('getAll')->willReturn([]);
        $this->categoryService->method('getByType')->willReturn(null);

        $result = $this->resource->onGet();

        $this->assertSame(200, $result->code);
        $this->assertSame([], $result->body['articles']);
        $this->assertSame('YouTube', $result->body['page_title']);
        $this->assertNull($result->body['category_id']);
    }

    public function testOnGetWhenYoutubeCategoryExistsCallsGetListWithCategoryId(): void
    {
        $youtubeCategory = ['id' => 5, 'name' => 'Videos', 'slug' => 'youtube', 'type' => 'youtube'];
        $articles        = [['id' => 10, 'title' => 'Video 1']];

        $this->categoryService->method('getAll')->willReturn([$youtubeCategory]);
        $this->categoryService->method('getByType')->with('youtube')->willReturn($youtubeCategory);
        $this->articleService->method('getList')
            ->with(1, 12, 5, 'published')
            ->willReturn(['items' => $articles, 'total' => 1, 'page' => 1, 'per_page' => 12, 'last_page' => 1]);

        $result = $this->resource->onGet(1, 12);

        $this->assertSame($articles, $result->body['articles']);
        $this->assertSame('Videos', $result->body['page_title']);
        $this->assertSame(5, $result->body['category_id']);
    }
}
