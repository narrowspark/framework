<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFactory\StreamFactory;

class ControllerClosureMiddleware implements MiddlewareInterface
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
