<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Delegate as DelegateContract;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;
use Viserio\Http\StreamFactory;

class FooMiddleware implements ServerMiddlewareContract
{
    public function process(
        ServerRequestInterface $request,
        DelegateContract $frame
    ): ResponseInterface {
        $request = $request->withAttribute('foo-middleware', 'foo-middleware');

        $response = $frame->next($request);

        return $response;
    }
}
