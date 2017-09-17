<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FooMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ): ResponseInterface {
        $request = $request->withAttribute('foo-middleware', 'foo-middleware');

        return $delegate->process($request);
    }
}
