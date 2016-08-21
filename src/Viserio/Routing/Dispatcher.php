<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Routing\Dispatcher as DispatcherContract;

class Dispatcher implements DispatcherContract
{
    use EventsAwareTrait;

    /**
     * The router instance.
     *
     * @var \Closure
     */
    protected $router;

    /**
     * Create a new Router instance.
     *
     * @param \Closure                            $path
     * @param \Viserio\Contracts\Middleware\Stack $middlewareDispatcher
     */
    public function __construct(Closure $router)
    {
        $this->router = $router;
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

        switch ($match[0]) {
            case DispatcherContract::NOT_FOUND:
                return $this->handleNotFound();
                break;
            case DispatcherContract::HTTP_METHOD_NOT_ALLOWED:
                return $this->handleNotAllowed($match[1]);
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
     */
    protected function handleFound(callable $route, array $vars)
    {
    }

    /**
     * Handle a not found route.
     */
    protected function handleNotFound()
    {
    }

    /**
     * Handles a not allowed route.
     *
     * @param array $allowed
     */
    protected function handleNotAllowed(array $allowed)
    {
    }
}
