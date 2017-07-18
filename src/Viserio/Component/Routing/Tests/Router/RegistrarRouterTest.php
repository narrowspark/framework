<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\RouteRegistrarControllerFixture;

class RegistrarRouterTest extends AbstractRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/users', 'controller'],
            ['GET', '/users/2', 'show'],
            ['GET', '/users/2/edit', 'edit'],
            ['POST', '/users', 'store'],
            ['PUT', '/users/1', 'update'],
            ['PATCH', '/users/1', 'update'],
            ['DELETE', '/users/1', 'deleted'],

            ['GET', '/prefix/user', 'controller'],

            ['GET', '/members', 'controller'],
            ['GET', '/members/1', 'show'],
            ['DELETE', '/members/1', 'deleted'],

            ['DELETE', '/blogs/1', 'deleted'],
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
            ['GET', '/blogs/1/edit'],
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

    public function testCanNameRoutesOnRegisteredResource()
    {
        $this->router->resource('users', RouteRegistrarControllerFixture::class)
            ->only(['create', 'store'])->addNames([
                'create' => 'user.build',
                'store'  => 'user.save',
            ]);
        $this->router->resource('posts', RouteRegistrarControllerFixture::class)
            ->only(['create', 'destroy'])
            ->setName('create', 'posts.make')
            ->setName('destroy', 'posts.remove');

        self::assertTrue($this->router->getRoutes()->hasNamedRoute('user.build'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('user.save'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('posts.make'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('posts.remove'));

        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest('GET', '/users')
        );
    }

    public function testCanOverrideParametersOnRegisteredResource()
    {
        $this->router->resource('users', RouteRegistrarControllerFixture::class)
            ->setParameters(['users' => 'admin_user']);
        $this->router->resource('posts', RouteRegistrarControllerFixture::class)
            ->addParameter('posts', 'topic');

        self::assertTrue($this->router->getRoutes()->getByName('users.show')->hasParameter('admin_user'));
        self::assertTrue($this->router->getRoutes()->getByName('posts.show')->hasParameter('topic'));

        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest('GET', '/users')
        );
    }

    public function testCanSetMiddlewareOnRegisteredResource()
    {
        $this->router->getContainer()->shouldReceive('has')
            ->with(FakeMiddleware::class)
            ->andReturn(true);
        $this->router->getContainer()->shouldReceive('get')
            ->with(FakeMiddleware::class)
            ->andReturn(new FakeMiddleware());

        $this->router->resource('users', RouteRegistrarControllerFixture::class)
            ->setMiddlewares(FakeMiddleware::class);

        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest('GET', '/users')
        );
    }

    public function testResourceRouting()
    {
        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['only' => ['show', 'destroy']]);
        $routes = $this->router->getRoutes();

        self::assertCount(15, $routes);

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['except' => ['show', 'destroy']]);
        $routes = $this->router->getRoutes();

        self::assertCount(20, $routes);

        $this->router->resource('baz-bars', RouteRegistrarControllerFixture::class, ['only' => ['show']]);
        $routes = $this->router->getRoutes();

        self::assertEquals('/baz-bars/{foo_bar}', $routes->match('GET|HEAD/baz-bars/{foo_bar}')->getUri());

        $this->router->resource('user-bars', RouteRegistrarControllerFixture::class, ['only' => ['show'], 'wildcards' => ['user-bars' => 'foo_bar_id']]);
        $routes = $this->router->getRoutes();

        self::assertEquals('/user-bars/{foo_bar_id}', $routes->match('GET|HEAD/user-bars/{foo_bar_id}')->getUri());

        $this->router->resource('member-bars.foo-bazs', RouteRegistrarControllerFixture::class, ['only' => ['show']]);
        $routes = $this->router->getRoutes();

        self::assertEquals('/member-bars/{member_bar}/foo-bazs/{foo_baz}', $routes->match('GET|HEAD/member-bars/{member_bar}/foo-bazs/{foo_baz}')->getUri());

        $this->router->resource('test-bars.test-bazs', RouteRegistrarControllerFixture::class, ['only' => ['show'], 'wildcards' => ['test-bars' => 'test_bar_id']]);
        $routes = $this->router->getRoutes();

        self::assertEquals('/test-bars/{test_bar_id}/test-bazs/{test_baz}', $routes->match('GET|HEAD/test-bars/{test_bar_id}/test-bazs/{test_baz}')->getUri());

        $this->router->resource('foo-bars.foo-bazs', RouteRegistrarControllerFixture::class, ['only' => ['show'], 'wildcards' => ['foo-bars' => 'foo_bar_id', 'foo-bazs' => 'foo_baz_id']]);
        $routes = $this->router->getRoutes();

        self::assertEquals('/foo-bars/{foo_bar_id}/foo-bazs/{foo_baz_id}', $routes->match('GET|HEAD/foo-bars/{foo_bar_id}/foo-bazs/{foo_baz_id}')->getUri());

        $this->router->resource('narrow-bars', RouteRegistrarControllerFixture::class, ['only' => ['show'], 'as' => 'prefix']);
        $routes = $this->router->getRoutes();

        self::assertEquals('/narrow-bars/{narrow_bar}', $routes->match('GET|HEAD/narrow-bars/{narrow_bar}')->getUri());
        self::assertEquals('prefix.narrow-bars.show', $routes->match('GET|HEAD/narrow-bars/{narrow_bar}')->getName());

        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest('GET', '/users')
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
            ->only(['index', 'show', 'destroy']);

        $router->resource('blogs', RouteRegistrarControllerFixture::class)
            ->except(['index', 'create', 'store', 'show', 'edit']);

        $router->resource('prefix/user', RouteRegistrarControllerFixture::class)
            ->only(['index']);
    }
}
