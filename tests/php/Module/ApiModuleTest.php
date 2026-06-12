<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Module;

use BEAR\Package\AbstractAppModule;
use Himatsudo\Module\ApiModule;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApiModuleTest extends TestCase
{
    public function testApiModuleClassExists(): void
    {
        $this->assertTrue(class_exists(ApiModule::class));
    }

    public function testApiModuleExtendsAbstractAppModule(): void
    {
        $ref = new ReflectionClass(ApiModule::class);
        $this->assertTrue($ref->isSubclassOf(AbstractAppModule::class));
    }
}
