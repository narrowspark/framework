<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\Http\StreamFactory;

class InlineParameterRouterTest extends RouteRouterBaseTest
{
    public function routerMatchingProvider(): array
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

    public function routerMatching404Provider()
    {
        return [
            ['GET', '/blog/posts'],
            ['GET', '/blog/post/abc!@#'],
            ['GET', '/blog/post/aBc'],
            ['GET', '/blog/post/another-123-post/comment/foo'],
            ['GET', '/blog/post/another-123-post/comment/'],
            ['GET', '/blog/post/another-123-post/comment/-1'],
        ];
    }

    public function routerMatching405Provider()
    {
        return [
            ['DELETE', '/'],
            ['PATCH', '/blog/post/123'],
            ['GET', '/blog/post/another-123-post/comment'],
            ['PUT', '/blog/post/another-123-post/comment'],
        ];
    }

    protected function definitions($router)
    {
        $router->get('/', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString('name = ' . $args['name']));
        })->setParameter('name', 'home');

        $router->get('/blog', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString('name = ' . $args['name']));
        })->setParameter('name', 'blog.index');
        $router->get('/blog/post/{post_slug:[a-z0-9\-]+}', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString('name = ' . $args['name'] . ' | post_slug = ' . $args['post_slug']));
        })->setParameter('name', 'blog.post.show');
        $router->post('/blog/post/{post_slug:[a-z0-9\-]+}/comment', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString('name = ' . $args['name'] . ' | post_slug = ' . $args['post_slug']));
        })->setParameter('name', 'blog.post.comment');
        $router->get('/blog/post/{post_slug:[a-z0-9\-]+}/comment/{comment_id:[0-9]+}', function ($request, $response, $args) {
            return $response->withBody((new StreamFactory())->createStreamFromString('name = ' . $args['name'] . ' | post_slug = ' . $args['post_slug'] . ' | comment_id = ' . $args['comment_id']));
        })->setParameter('name', 'blog.post.comment.show');
    }
}
