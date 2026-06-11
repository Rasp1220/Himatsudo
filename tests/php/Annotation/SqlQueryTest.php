<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Annotation;

use Attribute;
use Himatsudo\Annotation\SqlQuery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SqlQueryTest extends TestCase
{
    public function testConstructorSetsFileAndDefaultsParams(): void
    {
        $query = new SqlQuery('articles/list.sql');

        $this->assertSame('articles/list.sql', $query->file);
        $this->assertSame([], $query->params);
    }

    public function testConstructorSetsBothFileAndParams(): void
    {
        $query = new SqlQuery('users/get.sql', ['id', 'email']);

        $this->assertSame('users/get.sql', $query->file);
        $this->assertSame(['id', 'email'], $query->params);
    }

    public function testHasAttributeAnnotation(): void
    {
        $ref        = new ReflectionClass(SqlQuery::class);
        $attributes = $ref->getAttributes(Attribute::class);

        $this->assertNotEmpty($attributes, 'SqlQuery must be decorated with #[Attribute]');
    }
}
