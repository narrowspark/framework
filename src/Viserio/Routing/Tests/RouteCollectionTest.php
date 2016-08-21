<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Routing\RouteCollection;
use Viserio\Http\ServerRequestFactory;

class RouteCollectionTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testMatch()
    {
        $router = new RouteCollection(__DIR__.'/Cache/router.cache', $this->mock(ContainerInterface::class));
        $router->isDevelopMode(true);

        $router->get('/', function () {
            return 'hello';
        });

        // $this->assertEquals('hello', $router->dispatch((new ServerRequestFactory())->createServerRequest('GET', '/')));

        $router->get('/news/page/{slug}', function ($slug) {
            return $slug;
        });

        // $this->assertEquals('hello', $router->dispatch((new ServerRequestFactory())->createServerRequest('GET', '/news/123/hello')));
    }
}
