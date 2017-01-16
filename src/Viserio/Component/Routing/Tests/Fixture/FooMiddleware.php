<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class FooMiddleware implements ServerMiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) {
        $request = $request->withAttribute('foo-middleware', 'foo-middleware');

        $response = $delegate->process($request);

        return $response;
    }
}
