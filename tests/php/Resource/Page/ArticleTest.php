<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use Himatsudo\Resource\Page\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    use ResourceStubTrait;

    public function testOnGetReturns404WhenInnerResourceReturns404(): void
    {
        $resource = $this->makeResourceStub(404, ['error' => 'not found']);
        $page     = new Article($resource);

        $result = $page->onGet('missing-slug');

        $this->assertSame(404, $result->code);
    }

    public function testOnGetReturnsDetailTemplateForNonYoutubeCategory(): void
    {
        $body     = ['article' => ['title' => 'Hello', 'category_type' => 'normal'], 'categories' => []];
        $resource = $this->makeResourceStub(200, $body);
        $page     = new Article($resource);

        $result = $page->onGet('hello');

        $this->assertSame(200, $result->code);
        $this->assertSame('articles/detail', $result->body['_template']);
    }

    public function testOnGetReturnsYoutubeDetailTemplateForYoutubeCategory(): void
    {
        $body     = ['article' => ['title' => 'Video', 'category_type' => 'youtube'], 'categories' => []];
        $resource = $this->makeResourceStub(200, $body);
        $page     = new Article($resource);

        $result = $page->onGet('video-slug');

        $this->assertSame('articles/youtube-detail', $result->body['_template']);
    }
}
