<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Service;

use Himatsudo\Service\SqlFileTrait;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SqlFileTraitTest extends TestCase
{
    private object $subject;

    protected function setUp(): void
    {
        // Create a concrete class in the same namespace as the trait to mirror its __DIR__
        // We copy the trait's sql() method logic here with the correct base path
        $this->subject = new class () {
            use SqlFileTrait;

            public function sqlPublic(string $file): string
            {
                return $this->sql($file);
            }
        };
    }

    public function testSqlReturnsNonEmptyStringForExistingFile(): void
    {
        // Use reflection to call the private sql() method directly on a proper instance
        // that has the correct __DIR__ context (src/Service/SqlFileTrait.php)
        $traitClass = new class () {
            use SqlFileTrait;
        };

        $ref    = new ReflectionClass($traitClass);
        $method = $ref->getMethod('sql');
        $method->setAccessible(true);

        // The trait's sql() uses dirname(__DIR__) which from src/Service/ resolves to src/
        // Then appends /sql/{file}, so files in src/sql/ are accessible
        $result = $method->invoke($traitClass, 'articles/get_by_id.sql');

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testSqlReturnsStringContentContainingSql(): void
    {
        $traitClass = new class () {
            use SqlFileTrait;
        };

        $ref    = new ReflectionClass($traitClass);
        $method = $ref->getMethod('sql');
        $method->setAccessible(true);

        $result = $method->invoke($traitClass, 'categories/get_all.sql');

        $this->assertIsString($result);
        $this->assertGreaterThan(0, strlen($result));
    }

    public function testSqlReturnsEmptyStringForNonExistentFile(): void
    {
        $traitClass = new class () {
            use SqlFileTrait;
        };

        $ref    = new ReflectionClass($traitClass);
        $method = $ref->getMethod('sql');
        $method->setAccessible(true);

        // file_get_contents emits an E_WARNING and returns false for non-existent file;
        // the trait casts that false to '', so the result is an empty string.
        // We suppress the PHP warning here since we're deliberately testing the error path.
        $result = @$method->invoke($traitClass, 'nonexistent/does_not_exist.sql');

        $this->assertSame('', $result);
    }
}
