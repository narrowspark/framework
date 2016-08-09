<?php
declare(strict_types=1);
namespace Viserio\Routing;

use RapidRoute\{
    MatchResult,
    InvalidRouteDataException,
    Compilation\TreeBasedRouterCompiler
};
use Psr\Http\Message\ServerRequestInterface;

class Dispatcher
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
        $this->routes = new RouteCollection;
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
        $match = $this->match(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        switch ($match[0]) {
            case MatchResult::NOT_FOUND:
                // 404 Not Found...
                break;
            case MatchResult::HTTP_METHOD_NOT_ALLOWED:
                // 405 Method Not Allowed...
                break;
            case MatchResult::FOUND:
                // Matched route, dispatch to associated handler...
                break;
        }
    }

    /**
     * Get Route for given method and uri.
     *
     * @param string $httpMethod
     * @param string $uri
     *
     * @return \RapidRoute\MatchResult
     *
     * @throws \RapidRoute\InvalidRouteDataException
     */
    public function match(string $httpMethod, string $uri)
    {
        $compiledRouter = $this->generate();

        return MatchResult::fromArray($compiledRouter($httpMethod, $uri));
    }

    protected function generate()
    {
        $routerCompiler = new TreeBasedRouterCompiler();

        return $routerCompiler->compileRouter($this->routes);
    }
}
