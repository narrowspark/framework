<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Dispatchers;

use Narrowspark\HttpStatus\Exception\NotFoundException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

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
    public function setUp(): void
    {
        parent::setUp();

        $this->patch = self::normalizeDirectorySeparator(__DIR__ . '/../Cache');
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();

        if (\is_dir($this->patch)) {
            (new Filesystem())->remove($this->patch);
        }
    }

    /**
     * @expectedException \Narrowspark\HttpStatus\Exception\NotFoundException
     * @expectedExceptionMessage 404 Not Found: Requested route [/].
     */
    public function testHandleNotFound(): void
    {
        $collection = new RouteCollection();

        $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/')
        );
    }

    public function testHandleStrictMatching(): void
    {
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

        try {
            $this->dispatcher->handle(
                $collection,
                (new ServerRequestFactory())->createServerRequest('GET', '/test///')
            );
        } catch (NotFoundException $e) {
            self::assertSame('404 Not Found: Requested route [/test///].', $e->getMessage());
        }

        $response = $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test/')
        );

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @expectedException \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     * @expectedExceptionMessage 405 Method [GET,HEAD] Not Allowed: For requested route [/].
     */
    public function testHandleMethodNotAllowed(): void
    {
        $collection = new RouteCollection();
        $collection->add(new Route(
            'GET',
            '/',
            function () {
                return (new ResponseFactory())
                    ->createResponse()
                    ->withBody((new StreamFactory())->createStream('hello'));
            }
        ));

        $this->dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('DELETE', '/')
        );
    }
}
