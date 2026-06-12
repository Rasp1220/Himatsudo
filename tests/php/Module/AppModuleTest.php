<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Module;

use BEAR\Package\AbstractAppModule;
use Himatsudo\Module\AppModule;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AppModuleTest extends TestCase
{
    public function testAppModuleClassExists(): void
    {
        $this->assertTrue(class_exists(AppModule::class));
    }

    public function testAppModuleExtendsAbstractAppModule(): void
    {
        $ref = new ReflectionClass(AppModule::class);
        $this->assertTrue($ref->isSubclassOf(AbstractAppModule::class));
    }
}
