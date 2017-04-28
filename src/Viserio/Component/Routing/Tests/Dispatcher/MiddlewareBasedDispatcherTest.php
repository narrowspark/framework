<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Dispatchers;

use Psr\Http\Message\ResponseInterface;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatchers\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;

class MiddlewareBasedDispatcherTest extends AbstractDispatcherTest
{
    public function setUp()
    {
        $dispatcher  = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath(__DIR__ . '/../Cache/MiddlewareBasedDispatcherTest.cache');
        $dispatcher->refreshCache(true);

        $this->dispatcher = $dispatcher;
    }

    public function tearDown()
    {
    }

    public function testMiddlewareFunc()
    {
        $dispatcher = $this->dispatcher;

        $dispatcher->withMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $dispatcher->getMiddlewares());

        $dispatcher->setMiddlewarePriorities([999 => FooMiddleware::class]);

        self::assertSame([999 => FooMiddleware::class], $dispatcher->getMiddlewarePriorities());
    }

    public function testHandleFound()
    {
        $collection = new RouteCollection();
        $collection->add(new Route(
            'GET',
            '/test',
            [
                'uses' => function () {
                    return (new ResponseFactory())
                        ->createResponse()
                        ->withBody((new StreamFactory())->createStream('hello'));
                },
                'middlewares' => 'api',
            ]
        ));

        $this->dispatcher->setMiddlewareGroup('api', [new FakeMiddleware()]);

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test')
        );

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertSame('caught', (string) $response->getBody());
    }
}
