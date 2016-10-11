<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Fixture;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FooMiddleware implements ServerMiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $frame
    ): ResponseInterface {
        $request = $request->withAttribute('foo-middleware', 'foo-middleware');

        $response = $frame->next($request);

        return $response;
    }
}
