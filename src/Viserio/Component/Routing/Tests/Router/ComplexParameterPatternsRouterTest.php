<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Router;

use Viserio\Component\Contracts\Routing\Pattern;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;

class ComplexParameterPatternsRouterTest extends AbstractRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/a/prefix:abc', 'prefix | param = abc'],
            ['GET', '/a/prefix:aqwery12345', 'prefix | param = aqwery12345'],
            ['GET', '/b/abc:suffix', 'suffix | param = abc'],
            ['GET', '/b/aqwery12345:suffix', 'suffix | param = aqwery12345'],
            ['GET', '/c/prefix:abc:suffix', 'prefix-and-suffix | param = abc'],
            ['GET', '/c/prefix:aqwery12345:suffix', 'prefix-and-suffix | param = aqwery12345'],
            ['GET', '/d/abc-abc:abc', 'multi-param | param1 = abc | param2 = abc | param3 = abc'],
            ['GET', '/d/abc-def:ghi', 'multi-param | param1 = abc | param2 = def | param3 = ghi'],
            ['GET', '/e/123-abc:!!!', 'filtered-multi-param | digits = 123 | alpha = abc | exclaim = !!!'],
            ['GET', '/e/42-AaQqWw:!!!!!', 'filtered-multi-param | digits = 42 | alpha = AaQqWw | exclaim = !!!!!'],
            ['GET', '/f/jeff-is-awesome-at-soccer', 'sentence-multi-param | name = jeff | thing = soccer'],
            ['GET', '/f/Jeff-is-awesome-at-soccer', 'sentence-multi-param | name = Jeff | thing = soccer'],
            ['GET', '/f/Ben-is-awesome-at-tennis', 'sentence-multi-param | name = Ben | thing = tennis'],
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
            ['GET', 'a/'],
            ['GET', '/a/abc'],
            ['GET', '/a/prefix:'],
            ['GET', '/a//prefix:abc/'],
            ['GET', '/b/'],
            ['GET', '/b/abc'],
            ['GET', '/b/:suffix'],
            ['GET', '/c/'],
            ['GET', '/c/abc'],
            ['GET', '/c/:suffix'],
            ['GET', '/c/prefix:'],
            ['GET', '/c////prefix::suffix'],
            ['GET', '/d/'],
            ['GET', '/d/abc'],
            ['GET', '/d/-:'],
            ['GET', '/d/abc-'],
            ['GET', '/d/abc-:'],
            ['GET', '/d/abc-:abc'],
            ['GET', '/e/'],
            ['GET', '/e/abc'],
            ['GET', '/e/-:'],
            ['GET', '/e/abc-'],
            ['GET', '/e/abc-:'],
            ['GET', '/e/abc-:abc'],
            ['GET', '/e/alpha-abc:!!!'],
            ['GET', '/e/123-abc123:!!!'],
            ['GET', '/e/123-abc:!!'],
            ['GET', '/f/'],
            ['GET', '/f/abc'],
            ['GET', '/f/-is-awesome-at-soccer'],
            ['GET', '/f/jeff-is-awesome-at-'],
            ['GET', '/f/jeff-is-awesome-at-soCCer'],
            ['GET', '/f/jEff-is-awesome-at-soccer'],
            ['GET', '/f/123-is-awesome-at-soccer'],
            ['GET', '/f/jeff-is-awesome-at-abc123'],
        ];
    }

    protected function definitions(RouterContract $router)
    {
        $router->get('/a/prefix:{param}', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | param = ' . $args['param'])
            );
        })->addParameter('name', 'prefix');
        $router->get('/b/{param}:suffix', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | param = ' . $args['param'])
            );
        })->addParameter('name', 'suffix');
        $router->get('/c/prefix:{param}:suffix', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | param = ' . $args['param'])
            );
        })->addParameter('name', 'prefix-and-suffix');
        $router->get('/d/{param1}-{param2}:{param3}', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['name'] . ' | param1 = ' . $args['param1'] . ' | param2 = ' . $args['param2'] . ' | param3 = ' . $args['param3'])
            );
        })->addParameter('name', 'multi-param');
        $router->get('/e/{digits}-{alpha}:{exclaim}', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['routename'] . ' | digits = ' . $args['digits'] . ' | alpha = ' . $args['alpha'] . ' | exclaim = ' . $args['exclaim'])
            );
        })
            ->where('digits', Pattern::DIGITS)
            ->where('alpha', Pattern::ALPHA)
            ->where('exclaim', '!{3,5}')
            ->addParameter('routename', 'filtered-multi-param');
        $router->get('/f/{name}-is-awesome-at-{thing}', function ($request, $args) {
            return (new ResponseFactory())
            ->createResponse()
            ->withBody(
                (new StreamFactory())
                ->createStream($args['routename'] . ' | name = ' . $args['name'] . ' | thing = ' . $args['thing'])
            );
        })->where('name', '[A-Z]?[a-z]+')
            ->where('thing', Pattern::ALPHA_LOWER)
            ->addParameter('routename', 'sentence-multi-param');
    }
}
