<?php
declare(strict_types=1);
namespace Viserio\Middleware\Tests\Fixture;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;

class FakeContainerMiddleware implements ServerMiddlewareInterface
{
    use ContainerAwareTrait;

    public function process(
        ServerRequestInterface $request,
        DelegateInterface $frame
    ): ResponseInterface {
        $response = $frame->next($request);
        $response = $response->withAddedHeader('X-Foo', $this->getcontainer()->get('doo'));

        return $response;
    }
}
