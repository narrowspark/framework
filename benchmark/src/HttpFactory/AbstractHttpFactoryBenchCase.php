<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Narrowspark\Benchmark\HttpFactory;

/**
 * @BeforeMethods({"classSetUp"}, extend=true)
 * @Revs(3000)
 * @Iterations(10)
 * @OutputTimeUnit("microseconds", precision=3)
 */
abstract class AbstractHttpFactoryBenchCase
{
    /** @var \Psr\Http\Message\RequestFactoryInterface */
    protected $requestFactory;

    /** @var \Psr\Http\Message\ResponseFactoryInterface */
    protected $responseFactory;

    /** @var \Psr\Http\Message\StreamFactoryInterface */
    protected $streamFactory;

    /** @var \Psr\Http\Message\UriFactoryInterface */
    protected $uriFactory;

    abstract public function classSetUp(): void;

    /**
     * @Groups({"http-factory-request"})
     */
    public function benchRequestFactory(): void
    {
        $this->requestFactory->createRequest('POST', 'http://localhost.dev/foo?bar=2');
    }

    /**
     * @Groups({"http-factory-response"})
     */
    public function benchResponseFactory(): void
    {
        $this->responseFactory->createResponse(200, 'OK');
    }

    /**
     * @Groups({"http-factory-uri"})
     */
    public function benchUriFactories(): void
    {
        $this->uriFactory->createUri('http://localhost.dev/foo?bar=2');
    }

    /**
     * @Groups({"http-factory-stream"})
     */
    public function benchStreamFactories(): void
    {
        $this->streamFactory->createStream('content');
    }
}
