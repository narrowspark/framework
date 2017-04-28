<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Dispatchers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Dispatchers\MiddlewareBasedDispatcher;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Route\Collection as RouteCollection;
use Viserio\Component\Routing\Route;
use Viserio\Component\Routing\Tests\Fixture\Controller;
use Psr\Http\Message\ResponseInterface;

class SimpleDispatcherTest extends TestCase
{
    public function tearDown()
    {
        parent::tearDown();

        $this->delTree(__DIR__ . '/../Cache');
    }

    /**
     * @expectedException \Narrowspark\HttpStatus\Exception\NotFoundException
     * @expectedExceptionMessage 404 Not Found: Requested route [/]
     */
    public function testHandleNotFound()
    {
        $dispatcher  = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath(__DIR__ . '/../Cache/SimpleDispatcherTest.cache');
        $dispatcher->refreshCache(true);

        $collection = new RouteCollection();

        $response = $dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/')
        );
    }

    public function testHandleFound()
    {
        $dispatcher  = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath(__DIR__ . '/../Cache/SimpleDispatcherTest.cache');
        $dispatcher->refreshCache(true);

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

        $response = $dispatcher->handle(
            $collection,
            (new ServerRequestFactory())->createServerRequest('GET', '/test')
        );

        self::assertInstanceOf(ResponseInterface::class, $response);

        // $response = $dispatcher->handle(
        //     $collection,
        //     (new ServerRequestFactory())->createServerRequest('GET', '/test/')
        // );

        // self::assertInstanceOf(ResponseInterface::class, $response);
    }

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            is_dir("$dir/$file") ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
