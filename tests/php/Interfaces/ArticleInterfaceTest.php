<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Interfaces;

use Aura\Sql\ExtendedPdoInterface;
use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Service\ArticleService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ArticleInterfaceTest extends TestCase
{
    public function testArticleServiceImplementsArticleInterface(): void
    {
        $pdo     = $this->createMock(ExtendedPdoInterface::class);
        $service = new ArticleService($pdo);

        $this->assertInstanceOf(ArticleInterface::class, $service);
    }

    public function testArticleInterfaceHasRequiredMethods(): void
    {
        $ref     = new ReflectionClass(ArticleInterface::class);
        $methods = array_map(fn ($m) => $m->getName(), $ref->getMethods());

        $required = [
            'getList',
            'getAdminList',
            'getBySlug',
            'getById',
            'getLatest',
            'getLatestByCategory',
            'getLatestExcludeType',
            'create',
            'update',
            'delete',
        ];

        foreach ($required as $method) {
            $this->assertContains($method, $methods, "ArticleInterface must declare method '{$method}'");
        }
    }
}
