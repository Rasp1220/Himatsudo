<?php
declare(strict_types=1);

/** .env を読み込んで $_ENV に反映する（KEY=VALUE 形式のみ対応） */
function loadDotEnv(string $root): void
{
    $envFile = $root . '/.env';
    if (!file_exists($envFile)) {
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}
