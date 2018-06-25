<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Dispatcher;

use Psr\Http\Message\ResponseInterface;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Dispatcher\SimpleDispatcher;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Support\Invoker;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class SimpleDispatcherTest extends AbstractDispatcherTest
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var string
     */
    private $simpleDispatcherPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->simpleDispatcherPath = self::normalizeDirectorySeparator($this->patch . '/SimpleDispatcherTest.cache');

        $dispatcher = new SimpleDispatcher();
        $dispatcher->setCachePath($this->simpleDispatcherPath);
        $dispatcher->refreshCache(true);

        $this->dispatcher = $dispatcher;
    }

    public function testHandleFound(): void
    {
        $this->assertSame($this->simpleDispatcherPath, $this->dispatcher->getCachePath());

        $collection = new RouteCollection();
        $route      = new Route(
            'GET',
            '/test',
            function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody((new StreamFactory())->createStream('hello'));
            }
        );
        $route->setInvoker(new Invoker());

        $collection->add($route);

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test')
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test/')
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(Route::class, $this->dispatcher->getCurrentRoute());
    }
}
