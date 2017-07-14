<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Routing\Tests\Fixture\RouteRegistrarControllerFixture;

class RegistrarRouterTest extends AbstractRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['DELETE', 'users/1', 'deleted'],
            ['DELETE', 'members/1', 'deleted'],
            ['GET', 'members/1', 'show'],
            ['GET', 'members', 'controller'],
            ['DELETE', 'blogs/1', 'deleted'],
            ['PUT', '/blogs/1', 'update'],
        ];
    }

    public function routerMatching405Provider()
    {
        return [
            ['PUT', '/members'],
        ];
    }

    /**
     * @dataProvider routerMatching405Provider
     * @expectedException \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter405($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );
    }

    public function routerMatching404Provider()
    {
        return [
            ['GET', '/blogs'],
            ['POST', '/blogs/1/edit'],
        ];
    }

    /**
     * @dataProvider routerMatching404Provider
     * @expectedException \Narrowspark\HttpStatus\Exception\NotFoundException
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter404($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );
    }

    protected function definitions(RouterContract $router)
    {
        $router->getContainer()->shouldReceive('has')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(true);
        $router->getContainer()->shouldReceive('get')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(new RouteRegistrarControllerFixture());

        $router->resources(['users' => RouteRegistrarControllerFixture::class]);

        $router->resource('members', RouteRegistrarControllerFixture::class)
            ->only('index', 'show', 'destroy');
        $router->resource('blogs', RouteRegistrarControllerFixture::class)
            ->except(['index', 'create', 'store', 'show', 'edit']);
    }
}
