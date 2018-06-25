<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Dispatcher;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Support\Invoker;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
abstract class AbstractDispatcherTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

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

        $this->patch = self::normalizeDirectorySeparator(__DIR__ . '/../Cache');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if (\is_dir($this->patch)) {
            (new Filesystem())->remove($this->patch);
        }
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
            $this->assertSame('404 Not Found: Requested route [/test///].', $e->getMessage());
        }

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test/')
        );

        $this->assertInstanceOf(ResponseInterface::class, $response);
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
