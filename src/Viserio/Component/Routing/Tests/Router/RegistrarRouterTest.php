<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Routing\Tests\Fixture\RouteRegistrarControllerFixture;

class RegistrarRouterTest extends AbstractRouterBaseTest
{
    /**
     * @dataProvider routerMatchingProvider
     *
     * @param mixed      $httpMethod
     * @param mixed      $uri
     * @param mixed      $expectedResult
     * @param mixed      $status
     * @param null|mixed $middleware
     */
    public function testRouter($httpMethod, $uri, $expectedResult, $status = 200, $middleware = null)
    {
        $actualResult = $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );

        self::assertEquals($expectedResult, (string) $actualResult->getBody());
        self::assertSame($status, $actualResult->getStatusCode());

        if ($middleware !== null) {
            self::assertEquals($middleware, $this->router->getCurrentRoute()->getMiddlewares()[0]);
        }
    }

    protected function definitions($routes)
    {
        $routes->middleware('resource-middleware')
            ->resource('users', RouteRegistrarControllerFixture::class);
    }
}
