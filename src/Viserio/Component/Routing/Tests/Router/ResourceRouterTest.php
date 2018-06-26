<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\Routing\ResourceRegistrar;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\RouteRegistrarControllerFixture;

/**
 * @internal
 */
final class ResourceRouterTest extends AbstractRouterBaseTest
{
    /**
     * @return array
     */
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

    /**
     * @dataProvider routerMatching405Provider
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter405($httpMethod, $uri): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $this->definitions($this->router);

        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );
    }

    /**
     * @return array
     */
    public function routerMatching405Provider(): array
    {
        return [
            ['PUT', '/members'],
            ['PATCH', '/members'],
        ];
    }

    /**
     * @dataProvider routerMatching404Provider
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter404($httpMethod, $uri): void
    {
        $this->expectException(NotFoundException::class);

        $this->definitions($this->router);

        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );
    }

    /**
     * @return array
     */
    public function routerMatching404Provider(): array
    {
        return [
            ['GET', '/blogs'],
            ['GET', '/blogs/1/edit'],
        ];
    }

    public function testCanNameRoutesOnRegisteredResource(): void
    {
        $this->arrangeRegistrarController();

        $this->router->resource('users', RouteRegistrarControllerFixture::class)
            ->only(['create', 'store'])->addNames([
                'create' => 'user.build',
                'store'  => 'user.save',
            ]);
        $this->router->resource('posts', RouteRegistrarControllerFixture::class)
            ->only(['create', 'destroy'])
            ->setName('create', 'posts.make')
            ->setName('destroy', 'posts.remove');

        static::assertTrue($this->router->getRoutes()->hasNamedRoute('user.build'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('user.save'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('posts.make'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('posts.remove'));
    }

    public function testCanOverrideParametersOnRegisteredResource(): void
    {
        $this->arrangeRegistrarController();

        $this->router->resource('admin', RouteRegistrarControllerFixture::class)
            ->setParameters(['admin' => 'admin_user']);
        $this->router->resource('spark', RouteRegistrarControllerFixture::class)
            ->setParameter('spark', 'topic');

        static::assertSame('/admin/{admin_user}', $this->router->getRoutes()->getByName('admin.show')->getUri());
        static::assertSame('/spark/{topic}', $this->router->getRoutes()->getByName('spark.show')->getUri());
    }

    public function testCanSetAndRemoveMiddlewareOnRegisteredResource(): void
    {
        $this->arrangeRegistrarController();

        $this->containerMock->shouldReceive('has')
            ->with(FakeMiddleware::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->with(FakeMiddleware::class)
            ->andReturn(new FakeMiddleware());

        $this->router->resource('middleware', RouteRegistrarControllerFixture::class)
            ->withMiddleware(FakeMiddleware::class)
            ->withoutMiddleware(FakeMiddleware::class);

        $route = $this->router->getRoutes()->match('GET|HEAD/middleware');

        static::assertCount(1, $route->gatherMiddleware());
        static::assertCount(1, $route->gatherDisabledMiddleware());
    }

    public function testSingularParameters(): void
    {
        ResourceRegistrar::singularParameters(false);

        $this->router->resource('baz-bars', RouteRegistrarControllerFixture::class, ['only' => ['show']]);
        $routes = $this->router->getRoutes();

        static::assertEquals('/baz-bars/{baz_bars}', $routes->match('GET|HEAD/baz-bars/{baz_bars}')->getUri());

        ResourceRegistrar::singularParameters();
    }

    public function testResourceRouting(): void
    {
        $this->arrangeRegistrarController();

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['only' => ['show', 'destroy']]);
        $routes = $this->router->getRoutes();

        static::assertCount(2, $routes);

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['except' => ['show', 'destroy']]);
        $routes = $this->router->getRoutes();

        static::assertCount(7, $routes);

        $this->router->resource('user-bars', RouteRegistrarControllerFixture::class, ['only' => ['show'], 'wildcards' => ['user-bars' => 'foo_bar_id']]);
        $routes = $this->router->getRoutes();

        static::assertEquals('/user-bars/{foo_bar_id}', $routes->match('GET|HEAD/user-bars/{foo_bar_id}')->getUri());

        $this->router->resource('member-bars.foo-bazs', RouteRegistrarControllerFixture::class, ['only' => ['show']]);
        $routes = $this->router->getRoutes();

        static::assertEquals('/member-bars/{member_bar}/foo-bazs/{foo_baz}', $routes->match('GET|HEAD/member-bars/{member_bar}/foo-bazs/{foo_baz}')->getUri());

        $this->router->resource('test-bars.test-bazs', RouteRegistrarControllerFixture::class, ['only' => ['show'], 'wildcards' => ['test-bars' => 'test_bar_id']]);
        $routes = $this->router->getRoutes();

        static::assertEquals('/test-bars/{test_bar_id}/test-bazs/{test_baz}', $routes->match('GET|HEAD/test-bars/{test_bar_id}/test-bazs/{test_baz}')->getUri());

        $this->router->resource('foo-bars.foo-bazs', RouteRegistrarControllerFixture::class, ['only' => ['show'], 'wildcards' => ['foo-bars' => 'foo_bar_id', 'foo-bazs' => 'foo_baz_id']]);
        $routes = $this->router->getRoutes();

        static::assertEquals('/foo-bars/{foo_bar_id}/foo-bazs/{foo_baz_id}', $routes->match('GET|HEAD/foo-bars/{foo_bar_id}/foo-bazs/{foo_baz_id}')->getUri());

        $this->router->resource('narrow-bars', RouteRegistrarControllerFixture::class, ['only' => ['show'], 'as' => 'prefix']);
        $routes = $this->router->getRoutes();

        static::assertEquals('/narrow-bars/{narrow_bar}', $routes->match('GET|HEAD/narrow-bars/{narrow_bar}')->getUri());
        static::assertEquals('prefix.narrow-bars.show', $routes->match('GET|HEAD/narrow-bars/{narrow_bar}')->getName());

        $verbs = [
            'create' => 'ajouter',
            'edit'   => 'modifier',
        ];
        ResourceRegistrar::setVerbs($verbs);

        $this->router->resource('foo', RouteRegistrarControllerFixture::class);
        $routes = $this->router->getRoutes();

        static::assertSame($verbs, ResourceRegistrar::getVerbs());
        static::assertEquals('/foo/ajouter', $routes->getByName('foo.create')->getUri());
        static::assertEquals('/foo/{foo}/modifier', $routes->getByName('foo.edit')->getUri());
    }

    public function testSingularResourceRouting(): void
    {
        $this->arrangeRegistrarController();

        ResourceRegistrar::singularParameters();

        $this->router->resource('foos', RouteRegistrarControllerFixture::class);
        $this->router->resource('foos.bars', RouteRegistrarControllerFixture::class);
        $routes = $this->router->getRoutes();

        static::assertCount(14, $routes);
        static::assertEquals('/foos/{foo}', $routes->match('GET|HEAD/foos/{foo}')->getUri());
        static::assertEquals('/foos/{foo}/bars/{bar}', $routes->match('GET|HEAD/foos/{foo}/bars/{bar}')->getUri());
    }

    public function testSingularResourceRoutingWithParameters(): void
    {
        $param = ['foos' => 'oof', 'bazs' => 'b'];

        ResourceRegistrar::setParameters($param);

        static::assertSame($param, ResourceRegistrar::getParameters());

        $this->router->resource('bars.foos.bazs', RouteRegistrarControllerFixture::class);
        $routes = $this->router->getRoutes();

        static::assertCount(7, $routes);
        static::assertEquals('/bars/{bar}/foos/{oof}/bazs/{b}', $routes->match('GET|HEAD/bars/{bar}/foos/{oof}/bazs/{b}')->getUri());
    }

    public function testSingularResourceRoutingNoParametersAndNoSingularParameters(): void
    {
        ResourceRegistrar::setParameters();
        ResourceRegistrar::singularParameters(false);

        $this->router->resource('foos', RouteRegistrarControllerFixture::class, ['parameters' => 'singular']);
        $this->router->resource('foos.bars', RouteRegistrarControllerFixture::class, ['parameters' => 'singular']);
        $routes = $this->router->getRoutes();

        static::assertCount(14, $routes);
        static::assertEquals('/foos/{foo}', $routes->match('GET|HEAD/foos/{foo}')->getUri());
        static::assertEquals('/foos/{foo}/bars/{bar}', $routes->match('GET|HEAD/foos/{foo}/bars/{bar}')->getUri());

        $this->router->resource('foos.bars', RouteRegistrarControllerFixture::class, ['parameters' => ['foos' => 'foo', 'bars' => 'bar']]);
        $routes = $this->router->getRoutes();

        static::assertCount(14, $routes);
        static::assertEquals('/foos/{foo}/bars/{bar}', $routes->match('GET|HEAD/foos/{foo}/bars/{bar}')->getUri());
    }

    public function testResourceRouteNaming(): void
    {
        $this->arrangeRegistrarController();

        $this->router->resource('foo', RouteRegistrarControllerFixture::class);

        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.index'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.show'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.create'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.store'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.edit'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.update'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.destroy'));

        $this->router->resource('foo.bar', RouteRegistrarControllerFixture::class);

        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.index'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.show'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.create'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.store'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.edit'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.update'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.destroy'));

        $this->router->resource('prefix/foo.bar', RouteRegistrarControllerFixture::class);

        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.index'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.show'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.create'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.store'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.edit'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.update'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo.bar.destroy'));

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['names' => [
            'index' => 'foo',
            'show'  => 'bar',
        ]]);

        static::assertTrue($this->router->getRoutes()->hasNamedRoute('foo'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('bar'));

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['names' => 'bar']);

        static::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.index'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.show'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.create'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.store'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.edit'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.update'));
        static::assertTrue($this->router->getRoutes()->hasNamedRoute('bar.destroy'));
    }

    protected function definitions(RouterContract $router): void
    {
        $this->arrangeRegistrarController();

        $router->resources(['users' => RouteRegistrarControllerFixture::class]);

        $router->resource('members', RouteRegistrarControllerFixture::class)
            ->only(['index', 'show', 'destroy']);

        $router->resource('blogs', RouteRegistrarControllerFixture::class)
            ->except(['index', 'create', 'store', 'show', 'edit']);

        $router->resource('prefix/user', RouteRegistrarControllerFixture::class)
            ->only(['index']);
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    private function arrangeRegistrarController(): void
    {
        $this->containerMock->shouldReceive('has')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->with(RouteRegistrarControllerFixture::class)
            ->andReturn(new RouteRegistrarControllerFixture());
    }
}
