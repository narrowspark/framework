<?php
namespace Viserio\Middleware\Tests\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;

class FakeMiddleware2 implements ServerMiddlewareContract
{
    public function process(
        ServerRequestInterface $request,
        FrameContract $frame
    ): ResponseInterface {
        $response = $frame->next($request);

        return $response->withStatus(500);
    }
}
