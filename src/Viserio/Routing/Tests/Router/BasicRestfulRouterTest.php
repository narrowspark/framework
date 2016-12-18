<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\Contracts\Routing\Pattern;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;
use Viserio\HttpFactory\StreamFactory;

class BasicRestfulRouterTest extends RouteRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/user', 'user.index | '],
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
     * @expectedException \Narrowspark\HttpStatus\Exception\NotFoundException
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter404($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($_SERVER, $httpMethod, $uri),
            (new ResponseFactory())->createResponse()
        );
    }

    public function routerMatching404Provider()
    {
        return [
            ['GET', ''],
            ['GET', '/'],
            ['GET', '/users'],
            ['GET', '/users/1'],
            ['GET', '/user/'],
            ['GET', '/user/abc'],
            ['GET', '/user/-1'],
            ['GET', '/user/1.0'],
            ['GET', '/user/1/'],
            ['GET', '/user//edit'],
            ['GET', '/user/1/edit/'],
            ['GET', '/user/abc/edit'],
            ['GET', '/user/-1/edit'],
        ];
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

    /**
     * @dataProvider routerMatching405Provider
     * @expectedException \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter405($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($_SERVER, $httpMethod, $uri),
            (new ResponseFactory())->createResponse()
        );
    }

    protected function definitions($router)
    {
        $router->pattern('id', Pattern::DIGITS);
        $router->setParameter('digits', Pattern::DIGITS);

        $router->get('/user', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->setParameter('name', 'user.index');
        $router->get('/user/create', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->setParameter('name', 'user.create');
        $router->post('/user', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->setParameter('name', 'user.save');
        $router->get('/user/{id}', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->setParameter('name', 'user.show');
        $router->get('/user/{id}/edit', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->setParameter('name', 'user.edit');
        $router->put('/user/{id}', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->setParameter('name', 'user.update');
        $router->delete('/user/{id}', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->setParameter('name', 'user.delete');
        $router->patch('/admin/{id}', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | ' . ($args['id'] ?? ''))
            );
        })->setParameter('name', 'admin.patch');
        $router->options('/options', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | ' . $args['digits'])
            );
        })->setParameter('name', 'options');
    }
}
