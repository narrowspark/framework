<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Contracts\Routing\Pattern;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;

class ComplexShopRouterTest extends AbstractRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/', 'name = home'],

            ['GET', '/about-us', 'name = about-us'],

            ['GET', '/contact-us', 'name = contact-us'],
            ['POST', '/contact-us', 'name = contact-us.submit'],

            ['GET', '/blog', 'name = blog.index'],
            ['GET', '/blog/recent', 'name = blog.recent'],
            ['GET', '/blog/post/123', 'name = blog.post.show | post_slug = 123'],
            ['GET', '/blog/post/abc-123-qwerty', 'name = blog.post.show | post_slug = abc-123-qwerty'],
            ['POST', '/blog/post/cool-post/comment', 'name = blog.post.comment | post_slug = cool-post'],

            ['GET', '/shop', 'name = shop.index'],

            ['GET', '/shop/category', 'name = shop.category.index'],
            ['GET', '/shop/category/search/name:fun', 'name = shop.category.search | filter_by = name | filter_value = fun'],
            ['GET', '/shop/category/123', 'name = shop.category.show | category_id = 123'],
            ['GET', '/shop/category/123/product', 'name = shop.category.product.index | category_id = 123'],
            ['GET', '/shop/category/123/product/search/name:cool', 'name = shop.category.product.search | category_id = 123 | filter_by = name | filter_value = cool'],

            ['GET', '/shop/product', 'name = shop.product.index'],
            ['GET', '/shop/product/search/name:awesome', 'name = shop.product.search | filter_by = name | filter_value = awesome'],
            ['GET', '/shop/product/100', 'name = shop.product.show | product_id = 100'],

            ['GET', '/shop/cart', 'name = shop.cart.show'],
            ['PUT', '/shop/cart', 'name = shop.cart.add'],
            ['DELETE', '/shop/cart', 'name = shop.cart.empty'],
            ['GET', '/shop/cart/checkout', 'name = shop.cart.checkout.show'],
            ['POST', '/shop/cart/checkout', 'name = shop.cart.checkout.process'],

            ['GET', '/admin/login', 'name = admin.login'],
            ['POST', '/admin/login', 'name = admin.login.submit'],
            ['HEAD', '/admin/login', 'name = admin.login'],
            ['GET', '/admin/logout', 'name = admin.logout'],
            ['GET', '/admin', 'name = admin.index'],

            ['GET', '/admin/product', 'name = admin.product.index'],
            ['GET', '/admin/product/create', 'name = admin.product.create'],
            ['GET', '/admin/product/1', 'name = admin.product.show | product_id = 1'],
            ['HEAD', '/admin/product/123', 'name = admin.product.show | product_id = 123'],
            ['GET', '/admin/product/1/edit', 'name = admin.product.edit | product_id = 1'],
            ['PUT', '/admin/product/1', 'name = admin.product.update | product_id = 1'],
            ['PATCH', '/admin/product/1', 'name = admin.product.update | product_id = 1'],
            ['DELETE', '/admin/product/2', 'name = admin.product.destroy | product_id = 2'],

            ['GET', '/admin/category', 'name = admin.category.index'],
            ['GET', '/admin/category/create', 'name = admin.category.create'],
            ['GET', '/admin/category/1', 'name = admin.category.show | category_id = 1'],
            ['GET', '/admin/category/1/edit', 'name = admin.category.edit | category_id = 1'],
            ['PUT', '/admin/category/1', 'name = admin.category.update | category_id = 1'],
            ['PATCH', '/admin/category/1', 'name = admin.category.update | category_id = 1'],
            ['DELETE', '/admin/category/2', 'name = admin.category.destroy | category_id = 2'],
        ];
    }

    /**
     * @dataProvider routerMatching404Provider
     * @expectedException \Narrowspark\HttpStatus\Exception\NotFoundException
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter404($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );
    }

    public function routerMatching404Provider()
    {
        return [
            ['GET', '/blog/abc'],
            ['GET', '/shop/category/search/bad-prop:fun'],
            ['GET', '/shop/category/-1'],
            ['GET', '/shop/category/abc'],
            ['GET', '/shop/category/123/product/epic'],
            ['GET', '/shop/product/search/bad-prop:fun'],
            ['GET', '/shop/cart/checkout/abc'],
            ['GET', '/admin/logout/foo'],
            ['GET', '/admin/product/abc'],
            ['GET', '/admin/category/abc'],
        ];
    }

    public function routerMatching405Provider()
    {
        return [
            ['POST', '/about-us'],
            ['DELETE', '/contact-us'],
            ['PATCH', '/blog'],
            ['POST', '/blog/post/abc-123-qwerty'],
            ['GET', '/blog/post/cool-post/comment'],
            ['DELETE', '/shop'],
            ['PUT', '/shop/category'],
            ['PATCH', '/shop/category/123'],
            ['PUT', '/shop/product'],
            ['DELETE', '/shop/product/100'],
            ['PATCH', '/shop/cart'],
            ['PATCH', '/admin/login'],
            ['TRACE', '/admin/product'],
            ['POST', '/admin/product/create'],
            ['PATCH', '/admin/product/1/edit'],
            ['POST', '/admin/product/1'],
            ['TRACE', '/admin/product/123'],
            ['POST', '/admin/category/create'],
            ['PATCH', '/admin/category/1/edit'],
            ['POST', '/admin/category/1'],
        ];
    }

    /**
     * @dataProvider routerMatching405Provider
     * @expectedException \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter405($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($httpMethod, $uri)
        );
    }

    protected function definitions($router)
    {
        $router->pattern('post_slug', Pattern::ALPHA_NUM_DASH);
        $router->pattern('category_id', Pattern::DIGITS);
        $router->pattern('product_id', Pattern::DIGITS);
        $router->patterns(['filter_by' => Pattern::ALPHA]);

        self::assertSame(
            [
                'post_slug' => Pattern::ALPHA_NUM_DASH,
                'category_id' => Pattern::DIGITS,
                'product_id' => Pattern::DIGITS,
                'filter_by' => Pattern::ALPHA
            ],
            $router->getPatterns()
        );

        $router->get('/', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'home');
        $router->get('/about-us', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'about-us');
        $router->get('/contact-us', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'contact-us');
        $router->post('/contact-us', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'contact-us.submit');

        $router->get('/blog', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'blog.index');
        $router->get('/blog/recent', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'blog.recent');
        $router->get('/blog/post/{post_slug}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | post_slug = ' . $args['post_slug'])
                );
        })->setParameter('name', 'blog.post.show');
        $router->post('/blog/post/{post_slug}/comment', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | post_slug = ' . $args['post_slug'])
                );
        })->setParameter('name', 'blog.post.comment');

        $router->get('/shop', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'shop.index');

        $router->get('/shop/category', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'shop.category.index');
        $router->get('/shop/category/search/{filter_by}:{filter_value}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | filter_by = ' . $args['filter_by'] . ' | filter_value = ' . $args['filter_value'])
                );
        })->setParameter('name', 'shop.category.search');
        $router->get('/shop/category/{category_id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | category_id = ' . $args['category_id'])
                );
        })->setParameter('name', 'shop.category.show');
        $router->get('/shop/category/{category_id}/product', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | category_id = ' . $args['category_id'])
                );
        })->setParameter('name', 'shop.category.product.index');
        $router->get('/shop/category/{category_id}/product/search/{filter_by}:{filter_value}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | category_id = ' . $args['category_id'] . ' | filter_by = ' . $args['filter_by'] . ' | filter_value = ' . $args['filter_value'])
                );
        })->setParameter('name', 'shop.category.product.search');

        $router->get('/shop/product', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'shop.product.index');
        $router->get('/shop/product/search/{filter_by}:{filter_value}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | filter_by = ' . $args['filter_by'] . ' | filter_value = ' . $args['filter_value'])
                );
        })->setParameter('name', 'shop.product.search');
        $router->get('/shop/product/{product_id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | product_id = ' . $args['product_id'])
                );
        })->setParameter('name', 'shop.product.show');

        $router->get('/shop/cart', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'shop.cart.show');
        $router->put('/shop/cart', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'shop.cart.add');
        $router->delete('/shop/cart', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'shop.cart.empty');
        $router->get('/shop/cart/checkout', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'shop.cart.checkout.show');
        $router->post('/shop/cart/checkout', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'shop.cart.checkout.process');

        $router->get('/admin/login', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.login');
        $router->post('/admin/login', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.login.submit');
        $router->get('/admin/logout', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.logout');
        $router->get('/admin', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.index');

        $router->get('/admin/product', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.product.index');
        $router->get('/admin/product/create', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.product.create');
        $router->post('/admin/product', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.product.store');
        $router->get('/admin/product/{product_id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | product_id = ' . $args['product_id'])
                );
        })->setParameter('name', 'admin.product.show');
        $router->get('/admin/product/{product_id}/edit', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | product_id = ' . $args['product_id'])
                );
        })->setParameter('name', 'admin.product.edit');
        $router->match(['PUT', 'PATCH'], '/admin/product/{product_id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | product_id = ' . $args['product_id'])
                );
        })->setParameter('name', 'admin.product.update');
        $router->delete('/admin/product/{product_id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | product_id = ' . $args['product_id'])
                );
        })->setParameter('name', 'admin.product.destroy');

        $router->get('/admin/category', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.category.index');
        $router->get('/admin/category/create', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.category.create');
        $router->post('/admin/category', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'])
                );
        })->setParameter('name', 'admin.category.store');
        $router->get('/admin/category/{category_id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | category_id = ' . $args['category_id'])
                );
        })->setParameter('name', 'admin.category.show');
        $router->get('/admin/category/{category_id}/edit', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | category_id = ' . $args['category_id'])
                );
        })->setParameter('name', 'admin.category.edit');
        $router->match(['PUT', 'PATCH'], '/admin/category/{category_id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | category_id = ' . $args['category_id'])
                );
        })->setParameter('name', 'admin.category.update');
        $router->delete('/admin/category/{category_id}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream('name = ' . $args['name'] . ' | category_id = ' . $args['category_id'])
                );
        })->setParameter('name', 'admin.category.destroy');
    }
}
