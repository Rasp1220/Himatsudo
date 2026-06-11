<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Module;

use BEAR\Sunday\Extension\Application\AbstractApp;
use Himatsudo\Module\App;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AppTest extends TestCase
{
    public function testAppClassExists(): void
    {
        $this->assertTrue(class_exists(App::class));
    }

    public function testAppExtendsAbstractApp(): void
    {
        $ref = new ReflectionClass(App::class);
        $this->assertTrue($ref->isSubclassOf(AbstractApp::class));
    }
}
