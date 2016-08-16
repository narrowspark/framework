<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

use Psr\Http\Message\ServerRequestInterface;

interface Dispatcher
{
    const NOT_FOUND = 0;

    const FOUND = 1;

    const METHOD_NOT_ALLOWED = 2;

    /**
     * Match and dispatch a route matching the given http method and
     * uri, retruning an execution chain.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return mixed
     */
    public function handle(ServerRequestInterface $request);

    /**
     * Constructs a match results object from the supplied array.
     * The expected format is one of:
     *
     * [0 => MatchResult::FOUND, 1 => <route data>, 2 => <parameter array>]
     *
     * [0 => MatchResult::HTTP_METHOD_NOT_ALLOWED, 1 => <allowed http methods array>]
     *
     * [0 => MatchResult::NOT_FOUND]
     *
     * @param string $httpMethod
     * @param string $uri
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public function dispatch(string $httpMethod, string $uri): array;
}
