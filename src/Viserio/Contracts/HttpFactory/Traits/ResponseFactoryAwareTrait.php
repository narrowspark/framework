<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Traits;

use RuntimeException;
use Interop\Http\Factory\ResponseFactoryInterface;

trait ResponseFactoryAwareTrait
{
    /**
     * A ResponseFactory instance.
     *
     * @var \Interop\Http\Factory\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Set a ResponseFactory instance.
     *
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     *
     * @return $this
     */
    public function setResponseFactory(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;

        return $this;
    }

    /**
     * Get the ResponseFactory instance.
     *
     * @throws \RuntimeException
     *
     * @return \Interop\Http\Factory\ResponseFactoryInterface
     */
    public function getResponseFactory(): ResponseFactoryInterface
    {
        if (! $this->responseFactory) {
            throw new RuntimeException('ResponseFactory is not set up.');
        }

        return $this->responseFactory;
    }
}
