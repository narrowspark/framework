<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Contract\Routing\Router as RouterContract;

/**
 * @internal
 */
final class HttpMethodRouterTest extends AbstractRouterBaseTest
{
    /**
     * @return array
     */
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

    protected function definitions(RouterContract $router): void
    {
        $router->get('/', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                $this->streamFactory
                    ->createStream('name = ' . $name)
            );
        })->addParameter('name', 'home.get');

        $router->match(['POST', 'PATCH'], '/', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                $this->streamFactory
                    ->createStream('name = ' . $name)
            );
        })->addParameter('name', 'home.post-or-patch');

        $router->delete('/', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                $this->streamFactory
                    ->createStream('name = ' . $name)
            );
        })->addParameter('name', 'home.delete');

        $router->any('/', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                $this->streamFactory
                    ->createStream('name = ' . $name)
            );
        })->addParameter('name', 'home.fallback');
    }
}
