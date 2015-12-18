<?php
namespace Relay;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;

class FakeMiddleware implements MiddlewareContract
{
    public static $count = 0;

    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $response->getBody()->write(++ static::$count);
        $response = $next($request, $response);
        $response->getBody()->write(++ static::$count);

        return $response;
    }
}
