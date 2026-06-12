<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Service;

use Himatsudo\Service\YoutubeService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

class YoutubeServiceTest extends TestCase
{
    private YoutubeService $service;

    protected function setUp(): void
    {
        // Unset API key so no real HTTP call is made for the oembed path
        $_ENV['YOUTUBE_API_KEY'] = '';
        $this->service           = new YoutubeService();
    }

    public function testFetchVideoInfoThrowsForInvalidUrl(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid YouTube URL or video ID');

        $this->service->fetchVideoInfo('not-a-url-at-all');
    }

    private function callExtractVideoId(string $input): ?string
    {
        $ref    = new ReflectionClass($this->service);
        $method = $ref->getMethod('extractVideoId');
        $method->setAccessible(true);
        return $method->invoke($this->service, $input);
    }

    public function testExtractVideoIdFromPlainId(): void
    {
        $result = $this->callExtractVideoId('dQw4w9WgXcY');
        $this->assertSame('dQw4w9WgXcY', $result);
    }

    public function testExtractVideoIdFromYoutuBe(): void
    {
        $result = $this->callExtractVideoId('https://youtu.be/dQw4w9WgXcY');
        $this->assertSame('dQw4w9WgXcY', $result);
    }

    public function testExtractVideoIdFromYoutubeWatchUrl(): void
    {
        $result = $this->callExtractVideoId('https://www.youtube.com/watch?v=dQw4w9WgXcY');
        $this->assertSame('dQw4w9WgXcY', $result);
    }

    public function testExtractVideoIdFromEmbedUrl(): void
    {
        $result = $this->callExtractVideoId('https://www.youtube.com/embed/dQw4w9WgXcY');
        $this->assertSame('dQw4w9WgXcY', $result);
    }

    public function testExtractVideoIdReturnsNullForInvalidInput(): void
    {
        $result = $this->callExtractVideoId('not-a-valid-url');
        $this->assertNull($result);
    }
}
