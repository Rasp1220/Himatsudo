<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use finfo;

class Upload extends ResourceObject
{
    private const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    #[RequireAuth]
    public function onPost(string $data, string $mime = '', string $name = ''): static
    {
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            $this->code = 400;
            $this->body = ['error' => '画像ファイル（JPEG/PNG/GIF/WebP）のみアップロードできます'];
            return $this;
        }

        $imageData = base64_decode($data, true);
        if ($imageData === false || $imageData === '') {
            $this->code = 400;
            $this->body = ['error' => '無効なファイルデータです'];
            return $this;
        }

        // Verify actual content, not just the browser-supplied MIME type
        $finfo      = new finfo(FILEINFO_MIME_TYPE);
        $actualMime = $finfo->buffer($imageData);
        if (!in_array($actualMime, self::ALLOWED_MIME, true)) {
            $this->code = 400;
            $this->body = ['error' => '画像ファイル（JPEG/PNG/GIF/WebP）のみアップロードできます'];
            return $this;
        }

        $ext = match ($actualMime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => 'jpg',
        };

        $uploadDir = dirname(__DIR__, 5) . '/public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $path     = $uploadDir . $filename;

        if (file_put_contents($path, $imageData) === false) {
            $this->code = 500;
            $this->body = ['error' => 'ファイルの保存に失敗しました'];
            return $this;
        }

        $this->body = ['url' => '/uploads/' . $filename];
        return $this;
    }
}
