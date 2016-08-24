<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\Contracts\Routing\Dispatcher;
use Viserio\Http\StreamFactory;

class ComplexShopRouterTest #extends RouteRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
        ];
    }

    public function routerMatching404Provider()
    {
        return [
        ];
    }

    protected function definitions($router)
    {
    }
}
