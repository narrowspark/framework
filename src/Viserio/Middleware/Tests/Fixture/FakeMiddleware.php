<?php
namespace Viserio\Middleware\Tests\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;

class FakeMiddleware implements MiddlewareContract
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $response = $response->withAddedHeader('X-Foo', 'modified');
        $response = $next($request, $response, $next);

        return $response;
    }
}
