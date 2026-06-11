<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use Himatsudo\Resource\Page\Articles;
use PHPUnit\Framework\TestCase;

class ArticlesTest extends TestCase
{
    use ResourceStubTrait;

    public function testOnGetSetsBodyFromInnerResource(): void
    {
        $innerBody = [
            'articles'   => [],
            'total'      => 0,
            'page'       => 1,
            'per_page'   => 12,
            'last_page'  => 1,
            'categories' => [],
            'page_title' => '記事一覧',
        ];
        $resource = $this->makeResourceStub(200, $innerBody);
        $page     = new Articles($resource);

        $result = $page->onGet(1, null, 12);

        $this->assertSame(200, $result->code);
        $this->assertSame($innerBody, $result->body);
    }
}
