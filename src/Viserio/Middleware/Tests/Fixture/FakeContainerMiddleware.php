<?php
declare(strict_types=1);
namespace Viserio\Middleware\Tests\Fixture;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;

class FakeContainerMiddleware implements ServerMiddlewareContract
{
    /**
     * Container instance.
     *
     * @var \Interop\Container\ContainerInterface|null
     */
    protected $container;

    /**
     * Set a container.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container): ServerMiddlewareContract
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    public function process(
        ServerRequestInterface $request,
        FrameContract $frame
    ): ResponseInterface {
        $response = $frame->next($request);
        $response = $response->withAddedHeader('X-Foo', $this->getcontainer()->get('doo'));

        return $response;
    }
}
