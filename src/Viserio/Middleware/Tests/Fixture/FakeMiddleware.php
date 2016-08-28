<?php
declare(strict_types=1);
namespace Viserio\Middleware\Tests\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Delegate as DelegateContract;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;

class FakeMiddleware implements ServerMiddlewareContract
{
    public function process(
        ServerRequestInterface $request,
        DelegateContract $frame
    ): ResponseInterface {
        $response = $frame->next($request);

        $response = $response->withAddedHeader('X-Foo', 'modified');

        return $response;
    }
}
