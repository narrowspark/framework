<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Events\EventManager;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Router;

abstract class AbstractRouterBaseTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Contracts\Routing\Router
     */
    protected $router;

    public function setUp(): void
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

    public function tearDown(): void
    {
        parent::tearDown();
        $dir = __DIR__ . '/../Cache/';

        if (is_dir($dir)) {
            (new Filesystem())->remove($dir);
        }
    }

    /**
     * @dataProvider routerMatchingProvider
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     * @param mixed $expectedResult
     * @param mixed $status
     */
    public function testRouter($httpMethod, $uri, $expectedResult, $status = 200): void
    {
        $actualResult = $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );

        self::assertEquals($expectedResult, (string) $actualResult->getBody());
        self::assertSame($status, $actualResult->getStatusCode());
    }

    abstract protected function definitions(RouterContract $router);
}
