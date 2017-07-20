<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Routing\ResourceRegistrar;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\RouteRegistrarControllerFixture;

class ResourceRouterTest extends AbstractRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/users', 'controller'],
            ['GET', '/users/2', 'show'],
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
    public function testRouter405($httpMethod, $uri): void
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
    public function testRouter404($httpMethod, $uri): void
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );
    }

    public function testCanNameRoutesOnRegisteredResource(): void
    {
        $this->router->getContainer()->shouldReceive('has')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(true);
        $this->router->getContainer()->shouldReceive('get')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(new RouteRegistrarControllerFixture());

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
    }

    public function testCanOverrideParametersOnRegisteredResource(): void
    {
        $this->router->getContainer()->shouldReceive('has')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(true);
        $this->router->getContainer()->shouldReceive('get')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(new RouteRegistrarControllerFixture());

        $this->router->resource('admin', RouteRegistrarControllerFixture::class)
            ->setParameters(['admin' => 'admin_user']);
        $this->router->resource('spark', RouteRegistrarControllerFixture::class)
            ->addParameter('spark', 'topic');

        self::assertSame('/admin/{admin_user}', $this->router->getRoutes()->getByName('admin.show')->getUri());
        self::assertSame('/spark/{topic}', $this->router->getRoutes()->getByName('spark.show')->getUri());
    }

    public function testCanSetAndRemoveMiddlewareOnRegisteredResource(): void
    {
        $this->router->getContainer()->shouldReceive('has')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(true);
        $this->router->getContainer()->shouldReceive('get')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(new RouteRegistrarControllerFixture());

        $this->router->getContainer()->shouldReceive('has')
            ->with(FakeMiddleware::class)
            ->andReturn(true);
        $this->router->getContainer()->shouldReceive('get')
            ->with(FakeMiddleware::class)
            ->andReturn(new FakeMiddleware());

        $this->router->resource('middleware', RouteRegistrarControllerFixture::class)
            ->withMiddleware(FakeMiddleware::class)
            ->withoutMiddleware(FakeMiddleware::class);

        $route = $this->router->getRoutes()->match('GET|HEAD/middleware');

        self::assertCount(1, $route->gatherMiddleware());
        self::assertCount(1, $route->gatherDisabledMiddlewares());
    }

    public function testSingularParameters()
    {
        ResourceRegistrar::singularParameters(false);

        $this->router->resource('baz-bars', RouteRegistrarControllerFixture::class, ['only' => ['show']]);
        $routes = $this->router->getRoutes();

        self::assertEquals('/baz-bars/{baz_bars}', $routes->match('GET|HEAD/baz-bars/{baz_bars}')->getUri());

        ResourceRegistrar::singularParameters();
    }

    public function testResourceRouting(): void
    {
        $this->router->getContainer()->shouldReceive('has')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(true);
        $this->router->getContainer()->shouldReceive('get')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(new RouteRegistrarControllerFixture());

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['only' => ['show', 'destroy']]);
        $routes = $this->router->getRoutes();

        self::assertCount(15, $routes);

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['except' => ['show', 'destroy']]);
        $routes = $this->router->getRoutes();

        self::assertCount(20, $routes);

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

        $verbs = [
            'create' => 'ajouter',
            'edit'   => 'modifier',
        ];
        ResourceRegistrar::setVerbs($verbs);

        $this->router->resource('foo', RouteRegistrarControllerFixture::class);
        $routes = $this->router->getRoutes();

        self::assertSame($verbs, ResourceRegistrar::getVerbs());
        self::assertEquals('/foo/ajouter', $routes->getByName('foo.create')->getUri());
        self::assertEquals('/foo/{foo}/modifier', $routes->getByName('foo.edit')->getUri());
    }

    public function testResourceRoutingParameters()
    {
        $this->router->getContainer()->shouldReceive('has')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(true);
        $this->router->getContainer()->shouldReceive('get')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(new RouteRegistrarControllerFixture());

        ResourceRegistrar::singularParameters();

        $this->router->resource('foos', RouteRegistrarControllerFixture::class);
        $this->router->resource('foos.bars', RouteRegistrarControllerFixture::class);
        $routes = $this->router->getRoutes();

        self::assertEquals('/foos/{foo}', $routes->match('GET|HEAD/foos/{foo}')->getUri());
        self::assertEquals('/foos/{foo}/bars/{bar}', $routes->match('GET|HEAD/foos/{foo}/bars/{bar}')->getUri());

        $param = ['foos' => 'oof', 'bazs' => 'b'];
        ResourceRegistrar::setParameters($param);

        self::assertSame($param, ResourceRegistrar::getParameters());

        $this->router->resource('bars.foos.bazs', RouteRegistrarControllerFixture::class);
        $routes = $this->router->getRoutes();

        self::assertEquals('/bars/{bar}/foos/{oof}/bazs/{b}', $routes->match('GET|HEAD/bars/{bar}/foos/{oof}/bazs/{b}')->getUri());

        ResourceRegistrar::setParameters();
        ResourceRegistrar::singularParameters(false);

        $this->router->resource('foos', RouteRegistrarControllerFixture::class, ['parameters' => 'singular']);
        $this->router->resource('foos.bars', RouteRegistrarControllerFixture::class, ['parameters' => 'singular']);
        $routes = $this->router->getRoutes();

        self::assertEquals('/foos/{foo}', $routes->match('GET|HEAD/foos/{foo}')->getUri());
        self::assertEquals('/foos/{foo}/bars/{bar}', $routes->match('GET|HEAD/foos/{foo}/bars/{bar}')->getUri());

        $this->router->resource('foos.bars', RouteRegistrarControllerFixture::class, ['parameters' => ['foos' => 'foo', 'bars' => 'bar']]);
        $routes = $this->router->getRoutes();

        self::assertEquals('/foos/{foo}/bars/{bar}', $routes->match('GET|HEAD/foos/{foo}/bars/{bar}')->getUri());
    }

    public function testResourceRouteNaming()
    {
        $this->router->getContainer()->shouldReceive('has')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(true);
        $this->router->getContainer()->shouldReceive('get')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(new RouteRegistrarControllerFixture());
        $this->router->resource('foo', RouteRegistrarControllerFixture::class);

        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.index'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.show'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.create'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.store'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.edit'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.update'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.destroy'));

        $this->router->resource('foo.bar', RouteRegistrarControllerFixture::class);

        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.index'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.show'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.create'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.store'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.edit'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.update'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.destroy'));

        $this->router->resource('prefix/foo.bar', RouteRegistrarControllerFixture::class);

        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.index'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.show'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.create'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.store'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.edit'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.update'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.destroy'));

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['names' => [
            'index' => 'foo',
            'show'  => 'bar',
        ]]);

        self::assertTrue($this->router->getRoutes()->hasNamedRoute('foo'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('bar'));

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['names' => 'bar']);

        self::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.index'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.show'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.create'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.store'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.edit'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.update'));
        self::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.destroy'));
    }

    protected function definitions(RouterContract $router): void
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
