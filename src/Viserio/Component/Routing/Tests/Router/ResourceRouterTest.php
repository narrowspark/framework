<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Routing\ResourceRegistrar;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\RouteRegistrarControllerFixture;
use Viserio\Component\Routing\Tests\Router\Traits\TestRouter404Trait;
use Viserio\Component\Routing\Tests\Router\Traits\TestRouter405Trait;
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ResourceRouterTest extends AbstractRouterBaseTest
{
    use TestRouter404Trait;
    use TestRouter405Trait;

    public static function provideRouterCases(): iterable
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

    public static function provideRouter405Cases(): iterable
    {
        return [
            ['PUT', '/members'],
            ['PATCH', '/members'],
        ];
    }

    public static function provideRouter404Cases(): iterable
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
                'store' => 'user.save',
            ]);
        $this->router->resource('posts', RouteRegistrarControllerFixture::class)
            ->only(['create', 'destroy'])
            ->setName('create', 'posts.make')
            ->setName('destroy', 'posts.remove');

        $routes = $this->router->getRoutes();

        self::assertTrue($routes->hasNamedRoute('user.build'));
        self::assertTrue($routes->hasNamedRoute('user.save'));
        self::assertTrue($routes->hasNamedRoute('posts.make'));
        self::assertTrue($routes->hasNamedRoute('posts.remove'));
    }

    public function testCanOverrideParametersOnRegisteredResource(): void
    {
        $this->arrangeRegistrarController();

        $this->router->resource('admin', RouteRegistrarControllerFixture::class)
            ->setParameters(['admin' => 'admin_user']);
        $this->router->resource('spark', RouteRegistrarControllerFixture::class)
            ->setParameter('spark', 'topic');

        $routes = $this->router->getRoutes();

        self::assertSame('/admin/{admin_user}', $routes->getByName('admin.show')->getUri());
        self::assertSame('/spark/{topic}', $routes->getByName('spark.show')->getUri());
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

        self::assertCount(1, $route->gatherMiddleware());
        self::assertCount(1, $route->gatherDisabledMiddleware());
    }

    public function testSingularParameters(): void
    {
        ResourceRegistrar::singularParameters(false);

        $this->router->resource('baz-bars', RouteRegistrarControllerFixture::class, ['only' => ['show']]);

        $routes = $this->router->getRoutes();

        self::assertEquals('/baz-bars/{baz_bars}', $routes->match('GET|HEAD/baz-bars/{baz_bars}')->getUri());

        ResourceRegistrar::singularParameters();
    }

    public function testResourceRouting(): void
    {
        $this->arrangeRegistrarController();

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['only' => ['show', 'destroy']]);
        $routes = $this->router->getRoutes();

        self::assertCount(2, $routes);

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['except' => ['show', 'destroy']]);
        $routes = $this->router->getRoutes();

        self::assertCount(7, $routes);

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
            'edit' => 'modifier',
        ];
        ResourceRegistrar::setVerbs($verbs);

        $this->router->resource('foo', RouteRegistrarControllerFixture::class);
        $routes = $this->router->getRoutes();

        self::assertSame($verbs, ResourceRegistrar::getVerbs());
        self::assertEquals('/foo/ajouter', $routes->getByName('foo.create')->getUri());
        self::assertEquals('/foo/{foo}/modifier', $routes->getByName('foo.edit')->getUri());
    }

    public function testSingularResourceRouting(): void
    {
        $this->arrangeRegistrarController();

        ResourceRegistrar::singularParameters();

        $this->router->resource('foos', RouteRegistrarControllerFixture::class);
        $this->router->resource('foos.bars', RouteRegistrarControllerFixture::class);
        $routes = $this->router->getRoutes();

        self::assertCount(14, $routes);
        self::assertEquals('/foos/{foo}', $routes->match('GET|HEAD/foos/{foo}')->getUri());
        self::assertEquals('/foos/{foo}/bars/{bar}', $routes->match('GET|HEAD/foos/{foo}/bars/{bar}')->getUri());
    }

    public function testSingularResourceRoutingWithParameters(): void
    {
        $param = ['foos' => 'oof', 'bazs' => 'b'];

        ResourceRegistrar::setParameters($param);

        self::assertSame($param, ResourceRegistrar::getParameters());

        $this->router->resource('bars.foos.bazs', RouteRegistrarControllerFixture::class);
        $routes = $this->router->getRoutes();

        self::assertCount(7, $routes);
        self::assertEquals('/bars/{bar}/foos/{oof}/bazs/{b}', $routes->match('GET|HEAD/bars/{bar}/foos/{oof}/bazs/{b}')->getUri());
    }

    public function testSingularResourceRoutingNoParametersAndNoSingularParameters(): void
    {
        ResourceRegistrar::setParameters();
        ResourceRegistrar::singularParameters(false);

        $this->router->resource('foos', RouteRegistrarControllerFixture::class, ['parameters' => 'singular']);
        $this->router->resource('foos.bars', RouteRegistrarControllerFixture::class, ['parameters' => 'singular']);
        $routes = $this->router->getRoutes();

        self::assertCount(14, $routes);
        self::assertEquals('/foos/{foo}', $routes->match('GET|HEAD/foos/{foo}')->getUri());
        self::assertEquals('/foos/{foo}/bars/{bar}', $routes->match('GET|HEAD/foos/{foo}/bars/{bar}')->getUri());

        $this->router->resource('foos.bars', RouteRegistrarControllerFixture::class, ['parameters' => ['foos' => 'foo', 'bars' => 'bar']]);
        $routes = $this->router->getRoutes();

        self::assertCount(14, $routes);
        self::assertEquals('/foos/{foo}/bars/{bar}', $routes->match('GET|HEAD/foos/{foo}/bars/{bar}')->getUri());
    }

    public function testResourceRouteNaming(): void
    {
        $this->arrangeRegistrarController();

        $this->router->resource('foo', RouteRegistrarControllerFixture::class);

        $routes = $this->router->getRoutes();

        self::assertTrue($routes->hasNamedRoute('foo.index'));
        self::assertTrue($routes->hasNamedRoute('foo.show'));
        self::assertTrue($routes->hasNamedRoute('foo.create'));
        self::assertTrue($routes->hasNamedRoute('foo.store'));
        self::assertTrue($routes->hasNamedRoute('foo.edit'));
        self::assertTrue($routes->hasNamedRoute('foo.update'));
        self::assertTrue($routes->hasNamedRoute('foo.destroy'));

        $this->router->resource('foo.bar', RouteRegistrarControllerFixture::class);

        $routes = $this->router->getRoutes();

        self::assertTrue($routes->hasNamedRoute('foo.bar.index'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.show'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.create'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.store'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.edit'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.update'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.destroy'));

        $this->router->resource('prefix/foo.bar', RouteRegistrarControllerFixture::class);

        $routes = $this->router->getRoutes();

        self::assertTrue($routes->hasNamedRoute('foo.bar.index'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.show'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.create'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.store'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.edit'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.update'));
        self::assertTrue($routes->hasNamedRoute('foo.bar.destroy'));

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['names' => [
            'index' => 'foo',
            'show' => 'bar',
        ]]);

        $routes = $this->router->getRoutes();

        self::assertTrue($routes->hasNamedRoute('foo'));
        self::assertTrue($routes->hasNamedRoute('bar'));

        $this->router->resource('foo', RouteRegistrarControllerFixture::class, ['names' => 'bar']);

        $routes = $this->router->getRoutes();

        self::assertTrue($routes->hasNamedRoute('bar.index'));
        self::assertTrue($routes->hasNamedRoute('bar.show'));
        self::assertTrue($routes->hasNamedRoute('bar.create'));
        self::assertTrue($routes->hasNamedRoute('bar.store'));
        self::assertTrue($routes->hasNamedRoute('bar.edit'));
        self::assertTrue($routes->hasNamedRoute('bar.update'));
        self::assertTrue($routes->hasNamedRoute('bar.destroy'));
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
