<?php
namespace Viserio\Middleware\Tests\Fixture;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;

class FakeContainerMiddleware implements MiddlewareContract
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
     * @return self
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $response = $response->withAddedHeader('X-Foo', $this->getcontainer()->get('doo'));
        $response = $next($request, $response, $next);

        return $response;
    }
}
