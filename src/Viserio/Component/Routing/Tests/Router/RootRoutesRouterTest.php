<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Routing\Tests\Fixture\ControllerClosureMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FakeMiddleware;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;
use Viserio\Component\Routing\Tests\Fixture\InvokableActionFixture;
use Viserio\Component\Routing\Tests\Fixture\RouteTestClosureMiddlewareController;

class RootRoutesRouterTest extends RouteRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['Put', '', 'Hello'],
            ['GET', '', 'Hello'],
            ['GET', '/', 'Hello'],
            ['GET', '/middleware', 'caught'],
            ['GET', '/invoke', 'Hallo'],
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
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter404($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequestFromArray($_SERVER, $httpMethod, $uri)
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

        $router->get('foo/bar/%C3%A5%CE%B1%D1%84', function ($request, $args) {
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
            'uses'        => RouteTestClosureMiddlewareController::class . '@index',
            'middlewares' => FooMiddleware::class,
        ])->setParameter('name', 'middleware3');

        $router->get('/middleware4', [
            'uses'                => RouteTestClosureMiddlewareController::class . '@index',
            'middlewares'         => FooMiddleware::class,
            'without_middlewares' => FooMiddleware::class,
        ])->setParameter('name', 'middleware4');

        $router->getContainer()->shouldReceive('has')
            ->with(InvokableActionFixture::class)
            ->andReturn(true);
        $router->getContainer()->shouldReceive('get')
            ->with(InvokableActionFixture::class)
            ->andReturn(new InvokableActionFixture());

        $router->get('/invoke', ['uses' => InvokableActionFixture::class]);

        $router->group(['prefix' => 'all/'], __DIR__ . '/../Fixture/routes.php');
        $router->group(['prefix' => 'noslash'], __DIR__ . '/../Fixture/routes.php');
        $router->group(['prefix' => '/slash'], __DIR__ . '/../Fixture/routes.php');
    }
}
