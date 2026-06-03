<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page\Admin\Api;

use BEAR\Resource\ResourceObject;
use Himatsudo\Annotation\RequireAuth;
use finfo;

class Upload extends ResourceObject
{
    #[RequireAuth]
    public function onPost(): static
    {
        $file = $_FILES['file'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->code = 400;
            $this->body = ['error' => 'ファイルがアップロードされていません'];
            return $this;
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mimeType, $allowed, true)) {
            $this->code = 400;
            $this->body = ['error' => '画像ファイル（JPEG/PNG/GIF/WebP）のみアップロードできます'];
            return $this;
        }

        $ext = match ($mimeType) {
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

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            $this->code = 500;
            $this->body = ['error' => 'ファイルの保存に失敗しました'];
            return $this;
        }

        $this->body = ['url' => '/uploads/' . $filename];
        return $this;
    }
}
