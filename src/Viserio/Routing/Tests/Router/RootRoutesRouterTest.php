<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;
use Viserio\HttpFactory\StreamFactory;
use Viserio\Routing\Tests\Fixture\ControllerClosureMiddleware;
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
            ['HEAD', '/all/users', 'all-users'],
            ['HEAD', '/noslash/users', 'all-users'],
            ['HEAD', '/slash/users', 'all-users'],
        ];
    }

    /**
     * @dataProvider routerMatching404Provider
     * @expectedException \Narrowspark\HttpStatus\Exception\NotFoundException
     */
    public function testRouter404($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($_SERVER, $httpMethod, $uri),
            (new ResponseFactory())->createResponse()
        );
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

        $router->getContainer()->shouldReceive('has')
            ->with(FakeMiddleware::class)
            ->andReturn(true);
        $router->getContainer()->shouldReceive('get')
            ->with(FakeMiddleware::class)
            ->andReturn(new FakeMiddleware());

        $router->get('/middleware', ['middlewares' => FakeMiddleware::class, function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('Middleware')
                );
        }])->setParameter('name', 'middleware');

        $router->get('/middleware2', ['middlewares' => FakeMiddleware::class, 'uses' => function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('Middleware')
                );
        }])->setParameter('name', 'middleware2');

        $router->getContainer()->shouldReceive('has')
            ->with(ControllerClosureMiddleware::class)
            ->andReturn(true);
        $router->getContainer()->shouldReceive('get')
            ->with(ControllerClosureMiddleware::class)
            ->andReturn(new ControllerClosureMiddleware());
        $router->getContainer()->shouldReceive('get')
            ->with(RouteTestClosureMiddlewareController::class)
            ->andReturn(new RouteTestClosureMiddlewareController());
        $router->getContainer()->shouldReceive('has')
            ->with(FooMiddleware::class)
            ->andReturn(true);
        $router->getContainer()->shouldReceive('get')
            ->with(FooMiddleware::class)
            ->andReturn(new FooMiddleware());

        $router->get('/middleware3', [
            'uses' => RouteTestClosureMiddlewareController::class . '::index',
            'middlewares' => FooMiddleware::class,
        ])->setParameter('name', 'middleware3');

        $router->get('/middleware4', [
            'uses' => RouteTestClosureMiddlewareController::class . '::index',
            'middlewares' => FooMiddleware::class,
            'without_middlewares' => FooMiddleware::class,
        ])->setParameter('name', 'middleware4');

        $router->group(['prefix' => 'all/'], __DIR__ . '/../Fixture/routes.php');
        $router->group(['prefix' => 'noslash'], __DIR__ . '/../Fixture/routes.php');
        $router->group(['prefix' => '/slash'], __DIR__ . '/../Fixture/routes.php');
    }
}
