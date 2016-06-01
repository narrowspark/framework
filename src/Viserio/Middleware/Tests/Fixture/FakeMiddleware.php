<?php
namespace Viserio\Middleware\Tests\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;

class FakeMiddleware implements MiddlewareContract
{
    public function handle(
        ServerRequestInterface $request,
        FrameContract $frame
    ): ResponseInterface {
        $response = $frame->next($request);
        $response = $response->withAddedHeader('X-Foo', 'modified');

        return $response;
    }
}
