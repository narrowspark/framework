<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\Contracts\Routing\Dispatcher;
use Viserio\Http\StreamFactory;
use Viserio\Contracts\Routing\Pattern;

class BasicRestfulRouterTest extends RouteRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/user', 'user.index | '],
            ['GET', '/user/create', 'user.create | '],
            ['POST', '/user', 'user.save | '],
            ['GET', '/user/1', 'user.show | 1'],
            ['GET', '/user/123', 'user.show | 123'],
            ['HEAD', '/user/123', 'user.show | 123'],
            ['GET', '/user/0/edit', 'user.edit | 0'],
            ['GET', '/user/123/edit', 'user.edit | 123'],
            ['PUT', '/user/1', 'user.update | 1'],
        ];
    }

    public function routerMatching404Provider()
    {
        return [
            ['GET', ''],
            ['GET', '/'],
            ['GET', '/users'],
            ['GET', '/users/1'],
            ['GET', '/user/'],
            ['GET', '/user/abc'],
            ['GET', '/user/-1'],
            ['GET', '/user/1.0'],
            ['GET', '/user/1/'],
            ['GET', '/user//edit'],
            ['GET', '/user/1/edit/'],
            ['GET', '/user/abc/edit'],
            ['GET', '/user/-1/edit'],
        ];
    }

    public function routerMatching405Provider()
    {
        return [
            ['DELETE', '/user'],
            ['PATCH', '/user'],
            ['PUT', '/user'],
            ['DELETE', '/user'],
            ['POST', '/user/123'],
            ['PATCH', '/user/1/edit'],
            ['PATCH', '/user/1'],
            ['PATCH', '/user/123321'],
        ];
    }

    protected function definitions($router)
    {
        $router->pattern('id', Pattern::DIGITS);
        $router->get('/user', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString($args['name'] . ' | ' . ($args['id'] ?? '')));
        })->setParameter('name','user.index');
        $router->get('/user/create', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString($args['name'] . ' | ' . ($args['id'] ?? '')));
        })->setParameter('name','user.create');
        $router->post('/user', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString($args['name'] . ' | ' . ($args['id'] ?? '')));
        })->setParameter('name','user.save');
        $router->get('/user/{id}', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString($args['name'] . ' | ' . ($args['id'] ?? '')));
        })->setParameter('name','user.show');
        $router->get('/user/{id}/edit', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString($args['name'] . ' | ' . ($args['id'] ?? '')));
        })->setParameter('name','user.edit');
        $router->put('/user/{id}', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString($args['name'] . ' | ' . ($args['id'] ?? '')));
        })->setParameter('name','user.update');
        $router->delete('/user/{id}', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString($args['name'] . ' | ' . ($args['id'] ?? '')));
        })->setParameter('name','user.delete');
    }
}
