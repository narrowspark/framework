<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Dispatchers;

use Narrowspark\HttpStatus\Exception\NotFoundException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Route\Collection as RouteCollection;

abstract class AbstractDispatcherTest extends MockeryTestCase
{
    protected $dispatcher;

    public function tearDown(): void
    {
        parent::tearDown();

        $this->delTree(__DIR__ . '/../Cache');
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

    private function delTree($dir)
    {
        $files = \array_diff(\scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            \is_dir("$dir/$file") ? $this->delTree("$dir/$file") : \unlink("$dir/$file");
        }

        return \rmdir($dir);
    }
}
