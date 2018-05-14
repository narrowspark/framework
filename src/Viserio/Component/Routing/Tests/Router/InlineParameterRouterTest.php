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
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 *
 * @small
 */
final class InlineParameterRouterTest extends AbstractRouterBaseTest
{
    use TestRouter404Trait;
    use TestRouter405Trait;

    public function provideRouterCases(): iterable
    {
        return [
            ['GET', '', 'name = home'],
            ['GET', '/blog', 'name = blog.index'],
            ['GET', '/blog/post/some-post', 'name = blog.post.show | post_slug = some-post'],
            ['GET', '/blog/post/another-123-post', 'name = blog.post.show | post_slug = another-123-post'],
            ['POST', '/blog/post/some-post/comment', 'name = blog.post.comment | post_slug = some-post'],
            ['POST', '/blog/post/another-123-post/comment', 'name = blog.post.comment | post_slug = another-123-post'],
            ['GET', '/blog/post/another-123-post/comment/123', 'name = blog.post.comment.show | post_slug = another-123-post | comment_id = 123'],
        ];
    }

    public function provideRouter404Cases(): iterable
    {
        return [
            ['GET', '/blog/posts'],
            ['GET', '/blog/post/abc!@#'],
            ['GET', '/blog/post/aBc'],
            ['GET', '/blog/post/another-123-post/comment/foo'],
            ['GET', '/blog/post/another-123-post/comment/-1'],
        ];
    }

    public function provideRouter405Cases(): iterable
    {
        return [
            ['DELETE', '/'],
            ['PATCH', '/blog/post/123'],
            ['GET', '/blog/post/another-123-post/comment'],
            ['PUT', '/blog/post/another-123-post/comment'],
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
        })->addParameter('name', 'home');

        $router->get('/blog', function ($request, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name)
                );
        })->addParameter('name', 'blog.index');
        $router->get('/blog/post/{post_slug:[a-z0-9\-]+}', function ($request, $name, $post_slug) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | post_slug = ' . $post_slug)
                );
        })->addParameter('name', 'blog.post.show');
        $router->post('/blog/post/{post_slug:[a-z0-9\-]+}/comment', function ($request, $name, $post_slug) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | post_slug = ' . $post_slug)
                );
        })->addParameter('name', 'blog.post.comment');
        $router->get('/blog/post/{post_slug:[a-z0-9\-]+}/comment/{comment_id:[0-9]+}', function ($request, $name, $post_slug, $comment_id) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory
                        ->createStream('name = ' . $name . ' | post_slug = ' . $post_slug . ' | comment_id = ' . $comment_id)
                );
        })->addParameter('name', 'blog.post.comment.show');
    }
}
