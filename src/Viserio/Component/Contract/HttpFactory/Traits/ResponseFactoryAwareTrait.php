<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\HttpFactory\Traits;

use Psr\Http\Message\ResponseFactoryInterface;

trait ResponseFactoryAwareTrait
{
    /**
     * A ResponseFactory instance.
     *
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Set a ResponseFactory instance.
     *
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     *
     * @return $this
     */
    public function setResponseFactory(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;

        return $this;
    }
}
