<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\Http\ResponseFactory;
use Viserio\Http\StreamFactory;

class HttpMethodRouterTest extends RouteRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/', 'name = home.get'],
            ['HEAD', '/', 'name = home.get'],
            ['POST', '/', 'name = home.post-or-patch'],
            ['PATCH', '/', 'name = home.post-or-patch'],
            ['DELETE', '/', 'name = home.delete'],
            ['get', '/', 'name = home.get'],
            ['Get', '/', 'name = home.get'],
            ['Put', '/', 'name = home.fallback'],
        ];
    }

    protected function definitions($router)
    {
        $router->get('/', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStreamFromString('name = ' . $args['name'])
            );
        })->setParameter('name', 'home.get');

        $router->match(['POST', 'PATCH'], '/', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStreamFromString('name = ' . $args['name'])
            );
        })->setParameter('name', 'home.post-or-patch');

        $router->delete('/', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStreamFromString('name = ' . $args['name'])
            );
        })->setParameter('name', 'home.delete');

        $router->any('/', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStreamFromString('name = ' . $args['name'])
            );
        })->setParameter('name', 'home.fallback');
    }
}
