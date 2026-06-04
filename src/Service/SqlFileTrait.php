<?php
declare(strict_types=1);

namespace Himatsudo\Service;

trait SqlFileTrait
{
    private function sql(string $file): string
    {
        return (string) file_get_contents(dirname(__DIR__) . '/sql/' . $file);
    }
}
