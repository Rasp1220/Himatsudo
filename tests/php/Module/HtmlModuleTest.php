<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Module;

use BEAR\Package\AbstractAppModule;
use Himatsudo\Module\HtmlModule;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HtmlModuleTest extends TestCase
{
    public function testHtmlModuleClassExists(): void
    {
        $this->assertTrue(class_exists(HtmlModule::class));
    }

    public function testHtmlModuleExtendsAbstractAppModule(): void
    {
        $ref = new ReflectionClass(HtmlModule::class);
        $this->assertTrue($ref->isSubclassOf(AbstractAppModule::class));
    }
}
