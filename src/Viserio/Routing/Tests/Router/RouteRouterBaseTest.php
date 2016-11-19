<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use ReflectionClass;
use Viserio\Events\Dispatcher;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;
use Viserio\Routing\Router;

abstract class RouteRouterBaseTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected $router;

    public function setUp()
    {
        parent::setUp();

        $name = (new ReflectionClass($this))->getShortName();
        $container = $this->mock(ContainerInterface::class);
        $router = new Router($container);
        $router->setCachePath(__DIR__ . '/../Cache/' . $name . '.cache');
        $router->refreshCache(true);
        $router->setEventsDispatcher(new Dispatcher($container));

        $this->definitions($router);

        $this->router = $router;
    }

    /**
     * @dataProvider routerMatchingProvider
     */
    public function testRouter($httpMethod, $uri, $expectedResult, $status = 200)
    {
        $actualResult = $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($_SERVER, $httpMethod, $uri),
            (new ResponseFactory())->createResponse()
        );

        $this->assertEquals($expectedResult, (string) $actualResult->getBody());
        $this->assertSame($status, $actualResult->getStatusCode());
    }

    abstract protected function definitions($routes);
}
