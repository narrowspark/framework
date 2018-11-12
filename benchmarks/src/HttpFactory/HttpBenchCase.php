<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\HttpFactory;

/**
 * @BeforeMethods({"init"}, extend=true)
 * @Iterations(100)
 * @Revs({30})
 * @OutputTimeUnit("microseconds", precision=3)
 */
abstract class HttpBenchCase
{
    /**
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var \Psr\Http\Message\UriFactoryInterface
     */
    protected $uriFactory;

    abstract public function init(): void;

    /**
     * @Groups({"http-request"})
     */
    public function benchRequestFactory(): void
    {
        $this->requestFactory->createRequest('POST', 'http://localhost.dev/foo?bar=2');
    }

    /**
     * @Groups({"http-response"})
     */
    public function benchResponseFactory(): void
    {
        $this->responseFactory->createResponse(200, 'OK');
    }

    /**
     * @Groups({"http-uri"})
     */
    public function benchUriFactories(): void
    {
        $this->uriFactory->createUri('http://localhost.dev/foo?bar=2');
    }

    /**
     * @Groups({"http-stream"})
     */
    public function benchStreamFactories(): void
    {
        $this->streamFactory->createStream('content');
    }
}
