<?php
declare(strict_types=1);
namespace Viserio\Middleware\Tests\Fixture;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Middleware\Delegate as DelegateContract;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Middleware\ServerMiddleware as ServerMiddlewareContract;

class FakeContainerMiddleware implements ServerMiddlewareContract
{
    use ContainerAwareTrait;

    public function process(
        ServerRequestInterface $request,
        DelegateContract $frame
    ): ResponseInterface {
        $response = $frame->next($request);
        $response = $response->withAddedHeader('X-Foo', $this->getcontainer()->get('doo'));

        return $response;
    }
}
