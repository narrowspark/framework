<?php
declare(strict_types=1);
namespace Viserio\Middleware\Tests\Fixture;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class FakeMiddleware2 implements ServerMiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) {
        $response = $delegate->process($request);

        return $response->withStatus(500);
    }
}
