<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Annotation;

use Attribute;
use Himatsudo\Annotation\RequireAuth;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RequireAuthTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $instance = new RequireAuth();
        $this->assertInstanceOf(RequireAuth::class, $instance);
    }

    public function testHasAttributeAnnotation(): void
    {
        $ref        = new ReflectionClass(RequireAuth::class);
        $attributes = $ref->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes, 'RequireAuth must be decorated with #[Attribute]');
    }

    public function testAttributeTargetsMethodAndClass(): void
    {
        $ref        = new ReflectionClass(RequireAuth::class);
        $attributes = $ref->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes);

        /** @var Attribute $attrInstance */
        $attrInstance = $attributes[0]->newInstance();
        $expected     = Attribute::TARGET_METHOD | Attribute::TARGET_CLASS;

        $this->assertSame($expected, $attrInstance->flags);
    }
}
