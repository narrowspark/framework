<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFactory\StreamFactory;

class ControllerClosureMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ) {
        $response = $handler->handle($request);

        $response = $response->withBody((new StreamFactory())->createStream(
            $response->getBody() . '-' . $request->getAttribute('foo-middleware') . '-controller-closure'
        ));

        return $response;
    }
}
