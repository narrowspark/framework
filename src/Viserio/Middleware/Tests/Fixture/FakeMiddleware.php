<?php
namespace Viserio\Middleware\Tests\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;

class FakeMiddleware implements MiddlewareContract
{
    public function handle(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $response = $response->withAddedHeader('X-Foo', 'modified');
        $response = $next($request, $response, $next);

        return $response;
    }
}
