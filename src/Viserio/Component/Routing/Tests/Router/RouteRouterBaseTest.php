<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ReflectionClass;
use Viserio\Component\Events\EventManager;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Routing\Router;

abstract class RouteRouterBaseTest extends MockeryTestCase
{
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $cachefolder = __DIR__ . '/../Cache/';
        $name        = (new ReflectionClass($this))->getShortName();
        $container   = $this->mock(ContainerInterface::class);

        // if (! is_dir($cachefolder)) {
        //     mkdir($cachefolder);
        // }

        $router    = new Router($container);
        $router->setCachePath($cachefolder . $name . '.cache');
        $router->refreshCache(true);
        $router->setEventManager(new EventManager());

        $this->definitions($router);

        $this->router = $router;
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->delTree(__DIR__ . '/../Cache/');
    }

    /**
     * @dataProvider routerMatchingProvider
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     * @param mixed $expectedResult
     * @param mixed $status
     */
    public function testRouter($httpMethod, $uri, $expectedResult, $status = 200)
    {
        $actualResult = $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($_SERVER, $httpMethod, $uri),
            (new ResponseFactory())->createResponse()
        );

        self::assertEquals($expectedResult, (string) $actualResult->getBody());
        self::assertSame($status, $actualResult->getStatusCode());
    }

    abstract protected function definitions($routes);

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
