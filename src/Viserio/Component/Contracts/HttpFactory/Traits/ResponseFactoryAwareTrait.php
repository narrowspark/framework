<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Traits;

use Interop\Http\Factory\ResponseFactoryInterface;
use RuntimeException;

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
            throw new RuntimeException('Instance implementing \Interop\Http\Factory\ResponseFactoryInterface is not set up.');
        }

        return $this->responseFactory;
    }
}
