<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Dispatcher;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 */
abstract class AbstractDispatcherTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Contract\Routing\Dispatcher
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $patch;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->patch = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Cache';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        \array_map('unlink', \glob($this->patch . \DIRECTORY_SEPARATOR . '*'));

        @\rmdir($this->patch);
    }

    public function testHandleNotFound(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('404 Not Found: Requested route [/].');

        $collection = new RouteCollection();

        $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/')
        );
    }

    public function testHandleStrictMatching(): void
    {
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

        try {
            $this->dispatcher->handle(
                $collection,
                (new ServerRequestFactory())->createServerRequest('GET', '/test///')
            );
        } catch (NotFoundException $e) {
            static::assertSame('404 Not Found: Requested route [/test///].', $e->getMessage());
        }

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test/')
        );

        static::assertSame('hello', (string) $response->getBody());
    }

    public function testHandleMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedException::class);
        $this->expectExceptionMessage('405 Method [GET,HEAD] Not Allowed: For requested route [/].');

        $collection = new RouteCollection();
        $route      = new Route(
            'GET',
            '/',
            function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody((new StreamFactory())->createStream('hello'));
            }
        );
        $route->setInvoker(new Invoker());

        $collection->add($route);

        $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('DELETE', '/')
        );
    }
}
