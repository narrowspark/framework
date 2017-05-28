<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Viserio\Component\Events\EventManager;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Routing\Dispatchers\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Router;

abstract class AbstractRouterBaseTest extends MockeryTestCase
{
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $name = (new ReflectionClass($this))->getShortName();

        $dispatcher  = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath(__DIR__ . '/../Cache/' . $name . '.cache');
        $dispatcher->refreshCache(true);
        $dispatcher->setEventManager(new EventManager());

        $router = new Router($dispatcher);
        $router->setContainer($this->mock(ContainerInterface::class));

        $this->definitions($router);

        $this->router = $router;
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->delTree(__DIR__ . '/../Cache');
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
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );

        self::assertEquals($expectedResult, (string) $actualResult->getBody());
        self::assertSame($status, $actualResult->getStatusCode());
    }

    abstract protected function definitions($routes);

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            is_dir("$dir/$file") ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
