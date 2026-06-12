<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use Himatsudo\Resource\Page\Index;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    use ResourceStubTrait;

    public function testOnGetCallsInnerIndexResource(): void
    {
        $innerBody = [
            'latest_articles'          => [],
            'categories_with_articles' => [],
            'categories'               => [],
            'page_title'               => 'ホーム',
        ];
        $resource = $this->makeResourceStub(200, $innerBody);
        $page     = new Index($resource);

        $result = $page->onGet();

        $this->assertSame(200, $result->code);
        $this->assertSame($innerBody, $result->body);
    }
}
