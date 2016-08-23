<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\Contracts\Routing\Dispatcher;

class RootRoutesRouterTest extends RouteRouterBaseTest
{
    /**
     * Should return each case in the format:
     *
     * [
     *      'GET',
     *      '/user/1',
     *      Dispatcher::found(['route_data'], ['id' => '1'])
     * ]
     *
     * @return array[]
     */
    public function routerMatchingProvider()
    {
        return [
            ['GET', '', ['name' => 'root'], []],
            ['GET', '/', ['name' => 'root-slash'], []],
            ['GET', '/a', []],
            ['GET', 'test/123', []],
        ];
    }

    protected function definitions($router)
    {
        $router->get('')->setParameter('name', 'root');
        $router->get('/')->setParameter('name', 'root-slash');
        $router->get('/test/{param}');
    }
}
