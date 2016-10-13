<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Fixture;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\HttpFactory\StreamFactory;

class ControllerClosureMiddleware implements ServerMiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        DelegateInterface $delegate
    ) {
        $response = $delegate->process($request);

        $response = $response->withBody((new StreamFactory())->createStream(
            $response->getBody() . '-' . $request->getAttribute('foo-middleware') . '-controller-closure'
        ));

        return $response;
    }
}
