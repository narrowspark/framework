<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Routing\Tests\Fixture\ControllerClosureMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Component\Routing\Tests\Fixture\InvokableActionFixture;
use Viserio\Component\Routing\Tests\Fixture\RouteTestClosureMiddlewareController;
use Viserio\Component\Routing\Tests\Router\Traits\TestRouter404Trait;
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 *
 * @small
 */
final class RootRoutesRouterTest extends AbstractRouterBaseTest
{
    use TestRouter404Trait;

    public function provideRouterCases(): iterable
    {
        return [
            ['Put', '', 'Hello root'],
            ['GET', '', 'Hello root-slash'],
            ['GET', '/', 'Hello root-slash'],
            ['GET', '/middleware', 'caught'],
            ['GET', '/invoke', 'Hallo'],
            ['GET', '/middleware2', 'caught'],
            ['GET', '/foo/bar/åαф', 'Hello'],
            ['GET', '/middleware3', 'index-foo-middleware-controller-closure'],
            ['GET', '/middleware4', 'index--controller-closure'],
            ['GET', '/middleware5', 'index--controller-closure'],
            ['HEAD', '/all/users', 'all-users'],
            ['HEAD', '/noslash/users', 'all-users'],
            ['HEAD', '/slash/users', 'all-users'],
        ];
    }

    public function provideRouter404Cases(): iterable
    {
        return [
            ['GET', '/a'],
        ];
    }

    protected function definitions(RouterContract $router): void
    {
        $router->get('/', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream('Hello ' . $name)
                );
        })->addParameter('name', 'root-slash');

        $router->any('/', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream('Hello ' . $name)
                );
        })->addParameter('name', 'root');

        $router->get('foo/bar/%C3%A5%CE%B1%D1%84', function ($request) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream('Hello')
                );
        })->addParameter('name', 'root-slash');

        $this->arrangeMiddleware();

        $router->get('/middleware', ['middleware' => FakeMiddleware::class, function ($request) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream('Middleware')
                );
        }])->addParameter('name', 'middleware');

        $router->get('/middleware2', ['middleware' => FakeMiddleware::class, 'uses' => function ($request) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream('Middleware')
                );
        }])->addParameter('name', 'middleware2');

        $router->get('/middleware3', [
            'uses' => RouteTestClosureMiddlewareController::class . '@index',
            'middleware' => FooMiddleware::class,
        ])->addParameter('name', 'middleware3');

        $router->get('/middleware4', [
            'uses' => RouteTestClosureMiddlewareController::class . '@index',
            'middleware' => FooMiddleware::class,
            'bypass' => FooMiddleware::class,
        ])->addParameter('name', 'middleware4');

        $router->get('/middleware5', [
            'uses' => RouteTestClosureMiddlewareController::class . '@index',
            'middleware' => [FooMiddleware::class, FakeMiddleware::class],
            'bypass' => [FooMiddleware::class, FakeMiddleware::class],
        ])->addParameter('name', 'middleware5');

        $this->containerMock->shouldReceive('has')
            ->with(InvokableActionFixture::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->with(InvokableActionFixture::class)
            ->andReturn(new InvokableActionFixture());

        $router->get('/invoke', ['uses' => InvokableActionFixture::class]);

        $router->group(['prefix' => 'all/'], \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'routes.php');
        $router->group(['prefix' => 'noslash'], \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'routes.php');
        $router->group(['prefix' => '/slash'], \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'routes.php');
    }

    protected function arrangeMiddleware(): void
    {
        $this->containerMock->shouldReceive('has')
            ->with(ControllerClosureMiddleware::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->with(ControllerClosureMiddleware::class)
            ->andReturn(new ControllerClosureMiddleware());

        $this->containerMock->shouldReceive('has')
            ->with(RouteTestClosureMiddlewareController::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->with(RouteTestClosureMiddlewareController::class)
            ->andReturn(new RouteTestClosureMiddlewareController());

        $this->containerMock->shouldReceive('has')
            ->with(FooMiddleware::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->with(FooMiddleware::class)
            ->andReturn(new FooMiddleware());

        $this->containerMock->shouldReceive('has')
            ->with(FakeMiddleware::class)
            ->andReturn(true);
        $this->containerMock->shouldReceive('get')
            ->with(FakeMiddleware::class)
            ->andReturn(new FakeMiddleware());
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
