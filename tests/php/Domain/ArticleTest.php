<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Domain;

use Himatsudo\Domain\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    public function testFromArrayWithFullDataReturnsCorrectProperties(): void
    {
        $row = [
            'id'                => 5,
            'title'             => 'Test Title',
            'slug'              => 'test-title',
            'status'            => 'published',
            'content'           => '<p>content</p>',
            'blocks'            => '{"blocks":[]}',
            'excerpt'           => 'Short excerpt',
            'eye_catch_image'   => '/uploads/image.jpg',
            'category_id'       => 3,
            'category_name'     => 'Tech',
            'category_slug'     => 'tech',
            'category_type'     => 'normal',
            'author_id'         => 1,
            'author_name'       => 'Alice',
            'youtube_url'       => 'https://youtu.be/abc12345678',
            'youtube_video_id'  => 'abc12345678',
            'youtube_thumbnail' => 'https://img.youtube.com/vi/abc12345678/maxresdefault.jpg',
            'published_at'      => '2024-01-01 00:00:00',
            'created_at'        => '2024-01-01 00:00:00',
            'updated_at'        => '2024-06-01 00:00:00',
        ];

        $article = Article::fromArray($row);

        $this->assertSame(5, $article->id);
        $this->assertSame('Test Title', $article->title);
        $this->assertSame('test-title', $article->slug);
        $this->assertSame('published', $article->status);
        $this->assertSame('<p>content</p>', $article->content);
        $this->assertSame('{"blocks":[]}', $article->blocks);
        $this->assertSame('Short excerpt', $article->excerpt);
        $this->assertSame('/uploads/image.jpg', $article->eyeCatchImage);
        $this->assertSame(3, $article->categoryId);
        $this->assertSame('Tech', $article->categoryName);
        $this->assertSame('tech', $article->categorySlug);
        $this->assertSame('normal', $article->categoryType);
        $this->assertSame(1, $article->authorId);
        $this->assertSame('Alice', $article->authorName);
        $this->assertSame('https://youtu.be/abc12345678', $article->youtubeUrl);
        $this->assertSame('abc12345678', $article->youtubeVideoId);
        $this->assertSame('https://img.youtube.com/vi/abc12345678/maxresdefault.jpg', $article->youtubeThumbnail);
        $this->assertSame('2024-01-01 00:00:00', $article->publishedAt);
        $this->assertSame('2024-01-01 00:00:00', $article->createdAt);
        $this->assertSame('2024-06-01 00:00:00', $article->updatedAt);
    }

    public function testFromArrayWithMissingOptionalKeysDefaultsToNull(): void
    {
        $row = [
            'id'         => 1,
            'title'      => 'Minimal',
            'slug'       => 'minimal',
            'status'     => 'draft',
            'author_id'  => 2,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];

        $article = Article::fromArray($row);

        $this->assertNull($article->content);
        $this->assertNull($article->blocks);
        $this->assertNull($article->excerpt);
        $this->assertNull($article->eyeCatchImage);
        $this->assertNull($article->categoryId);
        $this->assertNull($article->categoryName);
        $this->assertNull($article->categorySlug);
        $this->assertNull($article->categoryType);
        $this->assertNull($article->authorName);
        $this->assertNull($article->youtubeUrl);
        $this->assertNull($article->youtubeVideoId);
        $this->assertNull($article->youtubeThumbnail);
        $this->assertNull($article->publishedAt);
    }

    public function testFromArrayWithEmptyArrayUsesDefaults(): void
    {
        $article = Article::fromArray([]);

        $this->assertSame(0, $article->id);
        $this->assertSame('', $article->title);
        $this->assertSame('', $article->slug);
        $this->assertSame('draft', $article->status);
        $this->assertSame(0, $article->authorId);
        $this->assertSame('', $article->createdAt);
        $this->assertSame('', $article->updatedAt);
    }

    public function testToArrayReturnsSnakeCaseKeys(): void
    {
        $row = [
            'id'                => 7,
            'title'             => 'Array Test',
            'slug'              => 'array-test',
            'status'            => 'published',
            'content'           => 'Body text',
            'blocks'            => null,
            'excerpt'           => null,
            'eye_catch_image'   => null,
            'category_id'       => 2,
            'category_name'     => 'News',
            'category_slug'     => 'news',
            'category_type'     => 'blog',
            'author_id'         => 1,
            'author_name'       => 'Bob',
            'youtube_url'       => null,
            'youtube_video_id'  => null,
            'youtube_thumbnail' => null,
            'published_at'      => '2024-03-01 00:00:00',
            'created_at'        => '2024-01-01 00:00:00',
            'updated_at'        => '2024-01-01 00:00:00',
        ];

        $article = Article::fromArray($row);
        $arr     = $article->toArray();

        $this->assertArrayHasKey('id', $arr);
        $this->assertArrayHasKey('eye_catch_image', $arr);
        $this->assertArrayHasKey('category_id', $arr);
        $this->assertArrayHasKey('category_name', $arr);
        $this->assertArrayHasKey('category_slug', $arr);
        $this->assertArrayHasKey('category_type', $arr);
        $this->assertArrayHasKey('author_id', $arr);
        $this->assertArrayHasKey('author_name', $arr);
        $this->assertArrayHasKey('youtube_url', $arr);
        $this->assertArrayHasKey('youtube_video_id', $arr);
        $this->assertArrayHasKey('youtube_thumbnail', $arr);
        $this->assertArrayHasKey('published_at', $arr);
        $this->assertArrayHasKey('created_at', $arr);
        $this->assertArrayHasKey('updated_at', $arr);
        $this->assertSame(7, $arr['id']);
        $this->assertSame('Array Test', $arr['title']);
        $this->assertSame('News', $arr['category_name']);
    }
}
