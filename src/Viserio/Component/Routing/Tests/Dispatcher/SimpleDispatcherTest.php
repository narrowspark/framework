<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Dispatchers;

use Psr\Http\Message\ResponseInterface;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatcher\SimpleDispatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class SimpleDispatcherTest extends AbstractDispatcherTest
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function setUp()
    {
        $dispatcher  = new SimpleDispatcher();
        $dispatcher->setCachePath(__DIR__ . '/../Cache/SimpleDispatcherTest.cache');
        $dispatcher->refreshCache(true);

        $this->dispatcher = $dispatcher;
    }

    public function testHandleFound()
    {
        $path = __DIR__ . '/../Cache/SimpleDispatcherTest.cache';

        self::assertSame(self::normalizeDirectorySeparator($path), $this->dispatcher->getCachePath());

        $collection = new RouteCollection();
        $collection->add(new Route(
            'GET',
            '/test',
            function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody((new StreamFactory())->createStream('hello'));
            }
        ));

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test')
        );

        self::assertInstanceOf(ResponseInterface::class, $response);

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test/')
        );

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertInstanceOf(Route::class, $this->dispatcher->getCurrentRoute());
    }
}
