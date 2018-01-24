<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\HttpFactory\StreamFactory;

class ControllerClosureMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $response = $handler->handle($request);

        $response = $response->withBody((new StreamFactory())->createStream(
            $response->getBody() . '-' . $request->getAttribute('foo-middleware') . '-controller-closure'
        ));

        return $response;
    }
}
