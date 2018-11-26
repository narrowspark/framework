<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Contract\Routing\Pattern;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Routing\Tests\Router\Traits\TestRouter404Trait;
use Viserio\Component\Routing\Tests\Router\Traits\TestRouter405Trait;

/**
 * @internal
 */
final class EdgeCasesRouterTest extends AbstractRouterBaseTest
{
    use TestRouter404Trait;
    use TestRouter405Trait;

    /**
     * @return array
     */
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/abc/a/bar', 'name = middle-param | param = a'],
            ['GET', '/123//bar', 'name = all-middle-param | param = '],
            ['GET', '/123/a/bar', 'name = all-middle-param | param = a'],
            ['GET', '/string', 'some-string'],
            ['GET', '/http/method/fallback', 'name = http-method-fallback.static'],
            ['POST', '/http/method/fallback', 'name = http-method-fallback.static.fallback'],
            ['DELETE', '/http/method/fallback', 'name = http-method-fallback.static.fallback'],
            ['DELETE', '/http/method/some-other', 'name = http-method-fallback.dynamic.fallback | parameter = some-other'],
            ['GET', '/allowed-methods/foo', 'name = allowed-methods.static'],
            ['POST', '/allowed-methods/bar', 'name = allowed-methods.dynamic | parameter = bar'],
            ['GET', '/complex-methods/123/foo/bar', 'name = complex-methods.first | param = 123'],
            ['POST', '/complex-methods/123/foo/bar', 'name = complex-methods.second | param = 123 | param2 = bar'],
            ['POST', '/complex-methods/123/bar', 'name = complex-methods.second | param = 123 | param2 = bar'],
        ];
    }

    /**
     * @return array
     */
    public function routerMatching404Provider(): array
    {
        return [
            ['GET', '/abc//bar'],
        ];
    }

    /**
     * @return array
     */
    public function routerMatching405Provider(): array
    {
        return [
            ['GET', '/allowed-methods/bar'],
            ['DELETE', '/allowed-methods/foo'],
            ['PATCH', '/complex-methods/123/foo/bar'],
            ['PATCH', '/complex-methods/abc123/foo/bar'],
            ['PATCH', '/complex-methods/123/foo/abc'],
        ];
    }

    protected function definitions(RouterContract $router): void
    {
        $router->get('/abc/{param}/bar', function ($request, $name, $param) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | param = ' . $param)
                );
        })->addParameter('name', 'middle-param');
        $router->get('/123/{param}/bar', function ($request, $name, $param) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | param = ' . $param)
                );
        })->where('param', '.*')->addParameter('name', 'all-middle-param');
        $router->get('/string', function ($request) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream('some-string')
                );
        });

        // Order of precedence:
        //  - static route
        //  - static without HTTP method
        //  - dynamic router
        //  - dynamic without HTTP method
        $router->get('/http/method/fallback', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name)
                );
        })->addParameter('name', 'http-method-fallback.static');
        $router->any('/http/method/fallback', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name)
                );
        })->addParameter('name', 'http-method-fallback.static.fallback');
        $router->post('/http/method/{parameter}', function ($request, $name, $parameter) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | parameter = ' . $parameter)
                );
        })->addParameter('name', 'http-method-fallback.dynamic');
        $router->any('/http/method/{parameter}', function ($request, $name, $parameter) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | parameter = ' . $parameter)
                );
        })->addParameter('name', 'http-method-fallback.dynamic.fallback');

        // Should detect allowed HTTP methods
        $router->get('/allowed-methods/foo', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name)
                );
        })->addParameter('name', 'allowed-methods.static');
        $router->post('/allowed-methods/{parameter}', function ($request, $name, $parameter) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | parameter = ' . $parameter)
                );
        })->addParameter('name', 'allowed-methods.dynamic');
        $router->get('/complex-methods/{param}/foo/bar', function ($request, $name, $param) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | param = ' . $param)
                );
        })->where('param', Pattern::DIGITS)->addParameter('name', 'complex-methods.first');
        $router->post('/complex-methods/{param}/foo/{param2}', function ($request, $name, $param, $param2) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | param = ' . $param . ' | param2 = ' . $param2)
                );
        })->where('param', Pattern::ALPHA_NUM)->addParameter('name', 'complex-methods.second');
        $router->post('/complex-methods/{param}/{param2}', function ($request, $name, $param, $param2) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | param = ' . $param . ' | param2 = ' . $param2)
                );
        })->where('param', Pattern::ALPHA_NUM)->addParameter('name', 'complex-methods.second');
    }
}
