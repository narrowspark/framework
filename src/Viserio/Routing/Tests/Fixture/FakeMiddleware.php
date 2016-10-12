<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Fixture;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\HttpFactory\StreamFactory;

class FakeMiddleware implements ServerMiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) {
        $response = $delegate->process($request);

        return $response->withBody(
            (new StreamFactory())
            ->createStream('caught')
        );
    }
}
