<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Routing\Dispatcher as DispatcherContract;

class Dispatcher implements DispatcherContract
{
    /**
     * The route collection instance.
     *
     * @var \Viserio\Routing\RouteCollection
     */
    protected $routes;

    /**
     * Create a new dispatcher instance.
     *
     * @param \Viserio\Routing\RouteCollection $routes
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = new RouteCollection();
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
        $match = $this->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        switch ($match[0]) {
            case DispatcherContract::NOT_FOUND:
                // 404 Not Found...
                break;
            case DispatcherContract::HTTP_METHOD_NOT_ALLOWED:
                // 405 Method Not Allowed...
                break;
            case DispatcherContract::FOUND:
                // Matched route, dispatch to associated handler...
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(string $httpMethod, string $uri): array
    {
    }

    protected function generate()
    {
    }
}
