<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

use Psr\Http\Message\ServerRequestInterface;

interface Dispatcher
{
    const NOT_FOUND = 0;

    const FOUND = 1;

    const HTTP_METHOD_NOT_ALLOWED = 2;

    /**
     * Match and dispatch a route matching the given http method and
     * uri, retruning an execution chain.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return mixed
     */
    public function handle(ServerRequestInterface $request);
}
