<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Contract\Routing\Pattern;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;

/**
 * @internal
 */
final class EdgeCasesRouterTest extends AbstractRouterBaseTest
{
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
     * @dataProvider routerMatching404Provider
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter404($httpMethod, $uri): void
    {
        $this->expectException(\Narrowspark\HttpStatus\Exception\NotFoundException::class);

        $this->definitions($this->router);

        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );
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

    /**
     * @dataProvider routerMatching405Provider
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter405($httpMethod, $uri): void
    {
        $this->expectException(\Narrowspark\HttpStatus\Exception\MethodNotAllowedException::class);

        $this->definitions($this->router);

        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );
    }

    protected function definitions(RouterContract $router): void
    {
        $router->get('/abc/{param}/bar', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'] . ' | param = ' . $args['param'])
                );
        })->addParameter('name', 'middle-param');
        $router->get('/123/{param}/bar', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'] . ' | param = ' . $args['param'])
                );
        })->where('param', '.*')->addParameter('name', 'all-middle-param');
        $router->get('/string', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('some-string')
                );
        });

        // Order of precedence:
        //  - static route
        //  - static without HTTP method
        //  - dynamic router
        //  - dynamic without HTTP method
        $router->get('/http/method/fallback', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'])
                );
        })->addParameter('name', 'http-method-fallback.static');
        $router->any('/http/method/fallback', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'])
                );
        })->addParameter('name', 'http-method-fallback.static.fallback');
        $router->post('/http/method/{parameter}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'] . ' | parameter = ' . $args['parameter'])
                );
        })->addParameter('name', 'http-method-fallback.dynamic');
        $router->any('/http/method/{parameter}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'] . ' | parameter = ' . $args['parameter'])
                );
        })->addParameter('name', 'http-method-fallback.dynamic.fallback');

        // Should detect allowed HTTP methods
        $router->get('/allowed-methods/foo', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'])
                );
        })->addParameter('name', 'allowed-methods.static');
        $router->post('/allowed-methods/{parameter}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'] . ' | parameter = ' . $args['parameter'])
                );
        })->addParameter('name', 'allowed-methods.dynamic');
        $router->get('/complex-methods/{param}/foo/bar', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'] . ' | param = ' . $args['param'])
                );
        })->where('param', Pattern::DIGITS)->addParameter('name', 'complex-methods.first');
        $router->post('/complex-methods/{param}/foo/{param2}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'] . ' | param = ' . $args['param'] . ' | param2 = ' . $args['param2'])
                );
        })->where('param', Pattern::ALPHA_NUM)->addParameter('name', 'complex-methods.second');
        $router->post('/complex-methods/{param}/{param2}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                        ->createStream('name = ' . $args['name'] . ' | param = ' . $args['param'] . ' | param2 = ' . $args['param2'])
                );
        })->where('param', Pattern::ALPHA_NUM)->addParameter('name', 'complex-methods.second');
    }
}
