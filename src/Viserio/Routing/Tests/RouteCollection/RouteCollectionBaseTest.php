<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\RouteCollection;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use ReflectionClass;
use Viserio\Http\ResponseFactory;
use Viserio\Http\ServerRequestFactory;
use Viserio\Routing\RouteCollection;

abstract class RouteCollectionBaseTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected $router;

    public function setUp()
    {
        parent::setUp();

        $name = (new ReflectionClass($this))->getShortName();
        $router = new RouteCollection(__DIR__ . '/../Cache/' . $name . '.cache', $this->mock(ContainerInterface::class));
        $router->isDevelopMode(true);

        $this->definitions($router);

        $this->router = $router;
    }

    /**
     * @dataProvider routerMatchingProvider
     */
    public function testRouter($httpMethod, $uri, $expectedResult)
    {
        // $actualResult = $this->router->dispatch(
        //     (new ServerRequestFactory())->createServerRequest($httpMethod, $uri),
        //     (new ResponseFactory())->createResponse()
        // );

        // $this->assertEquals($expectedResult, $actualResult);
    }

    abstract protected function definitions($routes);
}
