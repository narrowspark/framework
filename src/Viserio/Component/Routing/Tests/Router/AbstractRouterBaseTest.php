<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Events\EventManager;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
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
     * @var \Viserio\Component\HttpFactory\ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var \Viserio\Component\HttpFactory\ServerRequestFactory
     */
    protected $serverRequestFactory;

    /**
     * @var \Viserio\Component\HttpFactory\StreamFactory
     */
    protected $streamFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $name = (new ReflectionClass($this))->getShortName();

        $dispatcher = new MiddlewareBasedDispatcher();
        $dispatcher->setCachePath(__DIR__ . \DIRECTORY_SEPARATOR . 'Cache' . \DIRECTORY_SEPARATOR . $name . '.cache');
        $dispatcher->refreshCache(true);
        $dispatcher->setEventManager(new EventManager());

        $this->containerMock = $this->mock(ContainerInterface::class);

        $router = new Router($dispatcher);
        $router->setContainer($this->containerMock);

        $this->router               = $router;
        $this->responseFactory      = new ResponseFactory();
        $this->serverRequestFactory = new ServerRequestFactory();
        $this->streamFactory        = new StreamFactory();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $dir = __DIR__ . \DIRECTORY_SEPARATOR . 'Cache' . \DIRECTORY_SEPARATOR;

        \array_map(function ($value) {
            @\unlink($value);
        }, \glob($dir . \DIRECTORY_SEPARATOR . '*'));

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
            $this->serverRequestFactory->createServerRequest($httpMethod, $uri)
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
