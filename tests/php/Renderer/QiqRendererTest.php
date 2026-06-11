<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Renderer;

use BEAR\Resource\ResourceObject;
use Himatsudo\Renderer\QiqRenderer;
use Himatsudo\Resource\Page\Admin\Api\Article as AdminApiArticle;
use Himatsudo\Resource\Page\Article as PageArticle;
use Himatsudo\Resource\Page\Articles as PageArticles;
use Himatsudo\Resource\Page\Index as PageIndex;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class QiqRendererTest extends TestCase
{
    private ReflectionMethod $resolveTemplateName;
    private object $renderer;

    protected function setUp(): void
    {
        $ref = new ReflectionClass(QiqRenderer::class);
        // Create instance without running __construct (avoids Template::new() and mkdir)
        $this->renderer = $ref->newInstanceWithoutConstructor();

        $this->resolveTemplateName = $ref->getMethod('resolveTemplateName');
        $this->resolveTemplateName->setAccessible(true);
    }

    private function invoke(ResourceObject $ro): string
    {
        return $this->resolveTemplateName->invoke($this->renderer, $ro);
    }

    /**
     * Create a concrete anonymous instance of the given ResourceObject subclass
     * so that get_class() returns the correct fully-qualified name.
     */
    private function makeRo(string $class, array $body = []): ResourceObject
    {
        /** @var ResourceObject $ro */
        $ro = (new ReflectionClass($class))->newInstanceWithoutConstructor();

        // ResourceObject has public $body property
        $ro->body = $body;
        return $ro;
    }

    public function testReturnsTopIndexForPageIndex(): void
    {
        $ro     = $this->makeRo(PageIndex::class);
        $result = $this->invoke($ro);

        $this->assertSame('top/index', $result);
    }

    public function testReturnsArticlesIndexForPageArticles(): void
    {
        $ro     = $this->makeRo(PageArticles::class);
        $result = $this->invoke($ro);

        $this->assertSame('articles/index', $result);
    }

    public function testReturnsArticlesDetailForPageArticle(): void
    {
        $ro     = $this->makeRo(PageArticle::class);
        $result = $this->invoke($ro);

        $this->assertSame('articles/detail', $result);
    }

    public function testReturnsLowercasePathForAdminApiArticle(): void
    {
        $ro     = $this->makeRo(AdminApiArticle::class);
        $result = $this->invoke($ro);

        $this->assertSame('admin/api/article', $result);
    }

    public function testReturnsTemplateFromBodyWhenTemplateKeyPresent(): void
    {
        $ro     = $this->makeRo(PageIndex::class, ['_template' => 'articles/custom-view']);
        $result = $this->invoke($ro);

        $this->assertSame('articles/custom-view', $result);
    }
}
