<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page\Admin\Api;

use Himatsudo\Resource\Page\Admin\Api\Upload;
use PHPUnit\Framework\TestCase;

class UploadTest extends TestCase
{
    private Upload $resource;

    protected function setUp(): void
    {
        $this->resource = new Upload();
    }

    public function testOnPostReturns400ForDisallowedMimeType(): void
    {
        $result = $this->resource->onPost(base64_encode('some data'), 'application/pdf', 'file.pdf');

        $this->assertSame(400, $result->code);
        $this->assertArrayHasKey('error', $result->body);
    }

    public function testOnPostReturns400ForInvalidBase64Data(): void
    {
        $result = $this->resource->onPost('!!!not-valid-base64!!!', 'image/jpeg', 'file.jpg');

        $this->assertSame(400, $result->code);
    }

    public function testOnPostReturns400WhenBase64DecodesToWrongMime(): void
    {
        // Valid base64 but not a valid image (plain text)
        $fakeData = base64_encode('this is not an image at all, definitely not jpeg or png content here.');

        $result = $this->resource->onPost($fakeData, 'image/jpeg', 'file.jpg');

        $this->assertSame(400, $result->code);
    }

    public function testOnPostReturns200WithUrlForValidJpeg(): void
    {
        // Minimal JPEG-like bytes: starts with \xFF\xD8\xFF\xE0 + 8 zero bytes = 12 bytes
        $jpegBytes = "\xFF\xD8\xFF\xE0" . str_repeat("\x00", 8);
        $encoded   = base64_encode($jpegBytes);

        $result = $this->resource->onPost($encoded, 'image/jpeg', 'test.jpg');

        $this->assertSame(200, $result->code);
        $this->assertArrayHasKey('url', $result->body);
        $this->assertStringStartsWith('/uploads/', $result->body['url']);
    }
}
