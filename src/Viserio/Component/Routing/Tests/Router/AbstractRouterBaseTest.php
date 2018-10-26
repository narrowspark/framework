<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Events\EventManager;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Routing\Dispatcher\MiddlewareBasedDispatcher;
use Viserio\Component\Routing\Router;

/**
 * @internal
 */
abstract class AbstractRouterBaseTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Contract\Routing\Router
     */
    protected $router;

    /**
     * @var \Mockery\MockInterface|\Psr\Container\ContainerInterface
     */
    protected $containerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $name = (new ReflectionClass($this))->getShortName();

        $dispatcher = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Cache' . \DIRECTORY_SEPARATOR . $name . '.cache');
        $dispatcher->refreshCache(true);
        $dispatcher->setEventManager(new EventManager());

        $this->containerMock = $this->mock(ContainerInterface::class);

        $router = new Router($dispatcher);
        $router->setContainer($this->containerMock);

        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $dir = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Cache' . \DIRECTORY_SEPARATOR;

        \array_map('unlink', \glob($dir . \DIRECTORY_SEPARATOR . '*'));

        @\rmdir($dir);
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

        $this->assertEquals($expectedResult, (string) $actualResult->getBody());
        $this->assertSame($status, $actualResult->getStatusCode());
    }

    /**
     * @param \Viserio\Component\Contract\Routing\Router $router
     *
     * @return void
     */
    abstract protected function definitions(RouterContract $router): void;
}
