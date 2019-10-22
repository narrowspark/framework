<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Routing\Tests\Router\Traits\TestRouter404Trait;
use Viserio\Component\Routing\Tests\Router\Traits\TestRouter405Trait;
use Viserio\Contract\Routing\Pattern;
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 *
 * @small
 */
final class BasicRestfulRouterTest extends AbstractRouterBaseTest
{
    use TestRouter404Trait;
    use TestRouter405Trait;

    public function provideRouterCases(): iterable
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

    public function provideRouter404Cases(): iterable
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

    public function provideRouter405Cases(): iterable
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

        $router->get('/user', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | ')
                );
        })->addParameter('name', 'user.index');

        $router->get('/user/create', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | ')
                );
        })->addParameter('name', 'user.create');

        $router->post('/user', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | ')
                );
        })->addParameter('name', 'user.save');

        $router->get('/user/{id}', function ($request, $name, $id) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | ' . ($id ?? ''))
                );
        })->addParameter('name', 'user.show');

        $router->get('/user/{id}/edit', function ($request, $name, $id) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | ' . ($id ?? ''))
                );
        })->addParameter('name', 'user.edit');

        $router->put('/user/{id}', function ($request, $name, $id) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | ' . ($id ?? ''))
                );
        })->addParameter('name', 'user.update');

        $router->delete('/user/{id}', function ($request, $name, $id) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | ' . ($id ?? ''))
                );
        })->addParameter('name', 'user.delete');

        $router->patch('/admin/{id}', function ($request, $name, $id) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | ' . ($id ?? ''))
                );
        })->addParameter('name', 'admin.patch');

        $router->options('/options', function ($request, $name, $digits) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($name . ' | ' . $digits)
                );
        })->addParameter('name', 'options');
    }
}
