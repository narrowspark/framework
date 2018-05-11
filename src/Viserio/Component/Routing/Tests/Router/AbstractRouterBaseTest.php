<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Events\EventManager;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Router;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

abstract class AbstractRouterBaseTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Viserio\Component\Contract\Routing\Router
     */
    protected $router;

    /**
     * @var \Mockery\MockInterface|\Psr\Container\ContainerInterface
     */
    protected $containerMock;

    public function setUp(): void
    {
        parent::setUp();

        $name = (new ReflectionClass($this))->getShortName();

        $dispatcher = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath(self::normalizeDirectorySeparator(__DIR__ . '/../Cache/' . $name . '.cache'));
        $dispatcher->refreshCache(true);
        $dispatcher->setEventManager(new EventManager());

        $this->containerMock = $this->mock(ContainerInterface::class);

        $router = new Router($dispatcher);
        $router->setContainer($this->containerMock);

        $this->router = $router;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $dir = self::normalizeDirectorySeparator(__DIR__ . '/../Cache/');

        if (\is_dir($dir)) {
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
        $this->definitions($this->router);

        $actualResult = $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );

        self::assertEquals($expectedResult, (string) $actualResult->getBody());
        self::assertSame($status, $actualResult->getStatusCode());
    }

    abstract protected function definitions(RouterContract $router);
}
