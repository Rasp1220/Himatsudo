<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Interfaces;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Service\CategoryService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CategoryInterfaceTest extends TestCase
{
    public function testCategoryServiceImplementsCategoryInterface(): void
    {
        $pdo     = $this->createMock(ExtendedPdoInterface::class);
        $service = new CategoryService($pdo);

        $this->assertInstanceOf(CategoryInterface::class, $service);
    }

    public function testCategoryInterfaceHasRequiredMethods(): void
    {
        $ref     = new ReflectionClass(CategoryInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $ref->getMethods());

        $required = ['getAll', 'getById', 'getByType', 'getBySlug', 'create', 'update', 'delete'];

        foreach ($required as $method) {
            $this->assertContains($method, $methods, "CategoryInterface must declare method '{$method}'");
        }
    }
}
