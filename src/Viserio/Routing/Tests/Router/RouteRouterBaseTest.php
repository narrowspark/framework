<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use ReflectionClass;
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
        $router = new Router(__DIR__ . '/../Cache/' . $name . '.cache', $this->mock(ContainerInterface::class));
        $router->refreshCache(true);

        $this->definitions($router);

        $this->router = $router;
    }

    /**
     * @dataProvider routerMatchingProvider
     */
    public function testRouter($httpMethod, $uri, $expectedResult, $status = 200)
    {
        $actualResult = $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri),
            (new ResponseFactory())->createResponse()
        );

        $this->assertEquals($expectedResult, (string) $actualResult->getBody());
        $this->assertSame($status, $actualResult->getStatusCode());
    }

    /**
     * @dataProvider routerMatching404Provider
     * @expectedException \Narrowspark\HttpStatus\Exception\NotFoundException
     */
    public function testRouter404($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri),
            (new ResponseFactory())->createResponse()
        );
    }

    /**
     * @dataProvider routerMatching405Provider
     * @expectedException \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     */
    public function testRouter405($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri),
            (new ResponseFactory())->createResponse()
        );
    }

    abstract protected function definitions($routes);
}
