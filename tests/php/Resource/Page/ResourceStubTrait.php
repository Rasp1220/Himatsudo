<?php

declare(strict_types=1);

namespace Himatsudo\Tests\Resource\Page;

use BEAR\Resource\Method;
use BEAR\Resource\RequestInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use RuntimeException;

trait ResourceStubTrait
{
    /**
     * Create a concrete stub for ResourceInterface that handles the BEAR.Sunday fluent chain:
     * $resource->get->uri('...')->withQuery([...])->eager->request()
     *
     * The ResourceObject returned will have the given code and body.
     */
    private function makeResourceStub(int $code, mixed $body): ResourceInterface
    {
        $innerRo       = $this->createMock(ResourceObject::class);
        $innerRo->code = $code;
        $innerRo->body = $body;

        return new class ($innerRo) implements ResourceInterface {
            public function __construct(private readonly ResourceObject $ro)
            {
            }

            /** @phpstan-ignore-next-line */
            public function __get(string $name): object
            {
                $ro = $this->ro;

                return new class ($ro) {
                    public object $eager;

                    public function __construct(private readonly ResourceObject $ro)
                    {
                        $innerRo     = $ro;
                        $this->eager = new class ($innerRo) {
                            public function __construct(private readonly ResourceObject $ro)
                            {
                            }

                            public function request(): ResourceObject
                            {
                                return $this->ro;
                            }
                        };
                    }

                    public function uri(string $uri): static
                    {
                        return $this;
                    }

                    public function withQuery(array $q): static
                    {
                        return $this;
                    }

                    public function request(): ResourceObject
                    {
                        return $this->ro;
                    }
                };
            }

            public function newInstance($uri): ResourceObject
            {
                return $this->ro;
            }

            public function object(ResourceObject $ro): RequestInterface
            {
                return $this->makeRequest();
            }

            public function uri($uri): RequestInterface
            {
                return $this->makeRequest();
            }

            public function newRequest(Method $method, string $uri, array $query = []): RequestInterface
            {
                return $this->makeRequest();
            }

            public function crawl(string $uri, string $linkKey, array $query = []): ResourceObject
            {
                return $this->ro;
            }

            public function href(string $rel, array $query = [], ResourceObject|null $ro = null): ResourceObject
            {
                return $this->ro;
            }

            public function get(string $uri, array $query = []): ResourceObject
            {
                return $this->ro;
            }

            public function post(string $uri, array $query = []): ResourceObject
            {
                return $this->ro;
            }

            public function put(string $uri, array $query = []): ResourceObject
            {
                return $this->ro;
            }

            public function patch(string $uri, array $query = []): ResourceObject
            {
                return $this->ro;
            }

            public function delete(string $uri, array $query = []): ResourceObject
            {
                return $this->ro;
            }

            public function head(string $uri, array $query = []): ResourceObject
            {
                return $this->ro;
            }

            public function options(string $uri, array $query = []): ResourceObject
            {
                return $this->ro;
            }

            private function makeRequest(): RequestInterface
            {
                /** @phpstan-ignore-next-line */
                return new class () implements RequestInterface {
                    public function __invoke(array|null $query = null): ResourceObject
                    {
                        throw new RuntimeException('Not implemented');
                    }

                    public function withQuery(array $query): static
                    {
                        return $this;
                    }

                    public function addQuery(array $query): static
                    {
                        return $this;
                    }

                    public function toUri(): string
                    {
                        return '';
                    }

                    public function toUriWithMethod(): string
                    {
                        return '';
                    }

                    public function hash(): string
                    {
                        return '';
                    }

                    public function request(): mixed
                    {
                        return null;
                    }

                    public function linkSelf(string $linkKey): static
                    {
                        return $this;
                    }

                    public function linkNew(string $linkKey): static
                    {
                        return $this;
                    }

                    public function linkCrawl(string $linkKey): static
                    {
                        return $this;
                    }
                };
            }
        };
    }
}
