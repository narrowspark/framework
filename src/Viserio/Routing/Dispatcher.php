<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Routing\Dispatcher as DispatcherContract;

class Dispatcher implements DispatcherContract
{
    use ContainerAwareTrait;
    use EventsAwareTrait;

    /**
     * The router instance.
     *
     * @var object
     */
    protected $router;

    /**
     * Create a new Router instance.
     *
     * @param object                                $path
     * @param \Interop\Container\ContainerInterface $container
     * @param array                                 $options
     */
    public function __construct($router, ContainerInterface $container)
    {
        $this->router = $router;
        $this->container = $container;
    }

    /**
     * Match and dispatch a route matching the given http method and
     * uri, retruning an execution chain.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return mixed
     */
    public function handle(ServerRequestInterface $request)
    {
        $router = $this->router;
        $match = $router(
            $request->getMethod(),
            $request->getUri()->getPath()
        );
        var_dump($match);

        switch ($match[0]) {
            case DispatcherContract::NOT_FOUND:
                return $this->handleNotFound();
                break;
            case DispatcherContract::HTTP_METHOD_NOT_ALLOWED:
                $allowed = (array) $match[1];

                return $this->handleNotAllowed($allowed);
                break;
            case DispatcherContract::FOUND:

                return $this->handleFound($match[1], (array) $match[2]);
                break;
        }
    }

    /**
     * Handle dispatching of a found route.
     *
     * @param callable $route
     * @param array    $vars
     *
     * @return \League\Route\Middleware\ExecutionChain
     */
    protected function handleFound(callable $route, array $vars)
    {
    }

    /**
     * Handle a not found route.
     *
     * @return \League\Route\Middleware\ExecutionChain
     */
    protected function handleNotFound()
    {
    }

    /**
     * Handles a not allowed route.
     *
     * @param array $allowed
     *
     * @return \League\Route\Middleware\ExecutionChain
     */
    protected function handleNotAllowed(array $allowed)
    {
    }
}
