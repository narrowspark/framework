<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\StreamFactory;
use Viserio\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Routing\Tests\Fixture\RouteTestClosureMiddlewareController;

class RootRoutesRouterTest extends RouteRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['Put', '', 'Hello'],
            ['GET', '', 'Hello'],
            ['GET', '/', 'Hello'],
            ['GET', '/middleware', 'caught'],
            ['GET', '/middleware2', 'caught'],
            ['GET', '/foo/bar/åαф', 'Hello'],
            ['GET', '/middleware3', 'index-foo-middleware-controller-closure'],
            ['GET', '/middleware4', 'index--controller-closure'],
        ];
    }

    public function routerMatching404Provider()
    {
        return [
            ['GET', '/a'],
        ];
    }

    protected function definitions($router)
    {
        $router->any('/', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('Hello')
                );
        })->setParameter('name', 'root');

        $router->get('/', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('Hello')
                );
        })->setParameter('name', 'root');

        $router->get('/', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('Hello')
                );
        })->setParameter('name', 'root-slash');

        $router->get('foo/bar/åαф', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('Hello')
                );
        })->setParameter('name', 'root-slash');

        $router->get('/middleware', ['middleware.with' => new FakeMiddleware(), function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('Middleware')
                );
        }])->setParameter('name', 'middleware');
        $router->get('/middleware2', ['middleware.with' => new FakeMiddleware(), 'uses' => function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('Middleware')
                );
        }])->setParameter('name', 'middleware2');

        $router->getContainer()->shouldReceive('get')
            ->with(RouteTestClosureMiddlewareController::class)
            ->andReturn(new RouteTestClosureMiddlewareController());

        $router->get('/middleware3', [
            'uses' => RouteTestClosureMiddlewareController::class . '::index',
            'middleware.with' => new FooMiddleware(),
        ])->setParameter('name', 'middleware3');
        $router->get('/middleware4', [
            'uses' => RouteTestClosureMiddlewareController::class . '::index',
            'middleware.with' => new FooMiddleware(),
            'middleware.without' => new FooMiddleware(),
        ])->setParameter('name', 'middleware4');
    }
}
