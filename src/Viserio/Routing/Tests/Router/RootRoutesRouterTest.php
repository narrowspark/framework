<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\Contracts\Routing\Dispatcher;
use Viserio\Http\StreamFactory;

class RootRoutesRouterTest extends RouteRouterBaseTest
{
    /**
     * Should return each case in the format:
     *
     * [
     *      'GET',
     *      '/user/1',
     *      body string
     * ]
     *
     * @return array[]
     */
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '', 'Hello'],
            ['GET', '/', 'Hello'],
            ['GET', '/a', ''],
            ['GET', 'test/123', 'Hello, 123'],
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

        $router->get('/test/{param}', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString('Hello, ' . $args['param']));
        });
    }
}
