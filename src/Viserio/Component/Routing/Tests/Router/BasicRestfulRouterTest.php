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
final class BasicRestfulRouterTest extends AbstractRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/user', 'user.index | '],
            ['GET', '/user/', 'user.index | '],
            ['GET', '/user/create', 'user.create | '],
            ['POST', '/user', 'user.save | '],
            ['GET', '/user/1', 'user.show | 1'],
            ['GET', '/user/123', 'user.show | 123'],
            ['HEAD', '/user/123', 'user.show | 123'],
            ['GET', '/user/0/edit', 'user.edit | 0'],
            ['GET', '/user/123/edit', 'user.edit | 123'],
            ['PUT', '/user/1', 'user.update | 1'],
            ['PATCH', '/admin/1', 'admin.patch | 1'],
            ['OPTIONS', '/options', 'options | \d+'],
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

    public function routerMatching404Provider()
    {
        return [
            ['GET', ''],
            ['GET', '/'],
            ['GET', '/users'],
            ['GET', '/users/1'],
            ['GET', '/user/abc'],
            ['GET', '/user/-1'],
            ['GET', '/user/1.0'],
            ['GET', '/user/1///'],
            ['GET', '/user//edit'],
            ['GET', '/user/abc/edit'],
            ['GET', '/user/-1/edit'],
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

    protected function definitions(RouterContract $router): void
    {
        $router->pattern('id', Pattern::DIGITS);
        $router->addParameter('digits', Pattern::DIGITS);

        $router->get('/user', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                (new StreamFactory())
                    ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->addParameter('name', 'user.index');
        $router->get('/user/create', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                (new StreamFactory())
                    ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->addParameter('name', 'user.create');
        $router->post('/user', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                (new StreamFactory())
                    ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->addParameter('name', 'user.save');
        $router->get('/user/{id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                (new StreamFactory())
                    ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->addParameter('name', 'user.show');
        $router->get('/user/{id}/edit', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                (new StreamFactory())
                    ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->addParameter('name', 'user.edit');
        $router->put('/user/{id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                (new StreamFactory())
                    ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->addParameter('name', 'user.update');
        $router->delete('/user/{id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                (new StreamFactory())
                    ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->addParameter('name', 'user.delete');
        $router->patch('/admin/{id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                (new StreamFactory())
                    ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->addParameter('name', 'admin.patch');
        $router->options('/options', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                (new StreamFactory())
                    ->createStream($args['name'] . ' | ' . $args['digits'])
            );
        })->addParameter('name', 'options');
    }
}
