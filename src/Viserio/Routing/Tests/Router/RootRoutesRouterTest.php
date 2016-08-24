<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\Contracts\Routing\Dispatcher;
use Viserio\Http\StreamFactory;

class RootRoutesRouterTest extends RouteRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '', 'Hello'],
            ['GET', '/', 'Hello'],
        ];
    }

    public function routerMatching404Provider()
    {
        return [
            ['GET', '/a']
        ];
    }

    protected function definitions($router)
    {
        $router->get('', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString('Hello'));
        })->setParameter('name', 'root');

        $router->get('/', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString('Hello'));
        })->setParameter('name', 'root-slash');
    }
}
