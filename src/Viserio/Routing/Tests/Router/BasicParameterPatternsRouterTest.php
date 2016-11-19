<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Router;

use Viserio\Contracts\Routing\Pattern;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;
use Viserio\HttpFactory\StreamFactory;

class BasicParameterPatternsRouterTest extends RouteRouterBaseTest
{
    public function routerMatchingProvider(): array
    {
        return [
            ['GET', '/digits/0', '0 | digits'],
            ['GET', '/digits/00001', '00001 | digits'],
            ['GET', '/digits/1234', '1234 | digits'],

            ['GET', '/alpha/a', 'a | alpha'],
            ['GET', '/alpha/A', 'A | alpha'],
            ['GET', '/alpha/abcdefqwerty', 'abcdefqwerty | alpha'],
            ['GET', '/alpha/abcAdefCqwBerty', 'abcAdefCqwBerty | alpha'],

            ['GET', '/alpha_low/a', 'a | alpha_low'],
            ['GET', '/alpha_low/abcdefqwerty', 'abcdefqwerty | alpha_low'],

            ['GET', '/alpha_up/A', 'A | alpha_up'],
            ['GET', '/alpha_up/AWBCDEFG', 'AWBCDEFG | alpha_up'],

            ['GET', '/alpha_num/1', '1 | alpha_num'],
            ['GET', '/alpha_num/a', 'a | alpha_num'],
            ['GET', '/alpha_num/A', 'A | alpha_num'],
            ['GET', '/alpha_num/abcAdefCqwBerty', 'abcAdefCqwBerty | alpha_num'],
            ['GET', '/alpha_num/abcdef123qerty8', 'abcdef123qerty8 | alpha_num'],

            ['GET', '/alpha_num_dash/1', '1 | alpha_num_dash'],
            ['GET', '/alpha_num_dash/a', 'a | alpha_num_dash'],
            ['GET', '/alpha_num_dash/A', 'A | alpha_num_dash'],
            ['GET', '/alpha_num_dash/abcAdefCqwBerty', 'abcAdefCqwBerty | alpha_num_dash'],
            ['GET', '/alpha_num_dash/abcdef123qerty8', 'abcdef123qerty8 | alpha_num_dash'],
            ['GET', '/alpha_num_dash/ab2--3231c-dEf', 'ab2--3231c-dEf | alpha_num_dash'],

            ['GET', '/any/1', '1 | any'],
            ['GET', '/any/a', 'a | any'],
            ['GET', '/any/A', 'A | any'],
            ['GET', '/any/abcAdefCqwBerty', 'abcAdefCqwBerty | any'],
            ['GET', '/any/abcdef123qerty8', 'abcdef123qerty8 | any'],
            ['GET', '/any/ab2--3231c-dEf', 'ab2--3231c-dEf | any'],
            ['GET', '/any/abvdGF&##HGJD$%6%~jnk];[', 'abvdGF& | any'],

            ['GET', '/custom/!!!', '!!! | custom'],
            ['GET', '/custom/!!!!', '!!!! | custom'],
            ['GET', '/custom/!!!!!', '!!!!! | custom'],
        ];
    }

    /**
     * @dataProvider routerMatching404Provider
     * @expectedException \Narrowspark\HttpStatus\Exception\NotFoundException
     */
    public function testRouter404($httpMethod, $uri)
    {
        $this->router->dispatch(
            (new ServerRequestFactory())->createServerRequest($_SERVER,$httpMethod, $uri),
            (new ResponseFactory())->createResponse()
        );
    }

    public function routerMatching404Provider()
    {
        return [
            ['GET', '/digits/'],
            ['GET', '/digits/abc'],
            ['GET', '/digits/-1'],
            ['GET', '/digits/1.0'],
            ['GET', '/digits/123470!'],
            ['GET', '/digits/1234-70'],

            ['GET', '/alpha/'],
            ['GET', '/alpha/1'],
            ['GET', '/alpha/abc:def'],
            ['GET', '/alpha/abc-dEf'],
            ['GET', '/alpha/|'],

            ['GET', '/alpha_low/'],
            ['GET', '/alpha_low/1'],
            ['GET', '/alpha_low/abc:def'],
            ['GET', '/alpha_low/abc-dEf'],
            ['GET', '/alpha_low/A'],
            ['GET', '/alpha_low/abcAdefCqwBerty'],

            ['GET', '/alpha_up/'],
            ['GET', '/alpha_up/1'],
            ['GET', '/alpha_up/abc:def'],
            ['GET', '/alpha_up/abc-dEf'],
            ['GET', '/alpha_up/a'],
            ['GET', '/alpha_up/abcAdefCqwBerty'],

            ['GET', '/alpha_num/'],
            ['GET', '/alpha_num/abc:def'],
            ['GET', '/alpha_num/|'],

            ['GET', '/alpha_num_dash/'],
            ['GET', '/alpha_num_dash/abc:def'],
            ['GET', '/alpha_num_dash/|'],

            ['GET', '/any/'],

            ['GET', '/custom/'],
            ['GET', '/custom/abc:def'],
            ['GET', '/custom/|'],
            ['GET', '/custom/!'],
            ['GET', '/custom/!!'],
            ['GET', '/custom/abcde'],
            ['GET', '/custom/!!!a'],
            ['GET', '/custom/!!!!!!'],
        ];
    }

    protected function definitions($router)
    {
        $router->get('/digits/{param}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream($args['param'] . ' | ' . $args['name'])
                );
        })->where('param', Pattern::DIGITS)->setParameter('name', 'digits');

        $router->get('/alpha/{param}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream($args['param'] . ' | ' . $args['name'])
                );
        })->where('param', Pattern::ALPHA)->setParameter('name', 'alpha');

        $router->get('/alpha_low/{param}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream($args['param'] . ' | ' . $args['name'])
                );
        })->where('param', Pattern::ALPHA_LOWER)->setParameter('name', 'alpha_low');

        $router->get('/alpha_up/{param}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream($args['param'] . ' | ' . $args['name'])
                );
        })->where('param', Pattern::ALPHA_UPPER)->setParameter('name', 'alpha_up');

        $router->get('/alpha_num/{param}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream($args['param'] . ' | ' . $args['name'])
                );
        })->where('param', Pattern::ALPHA_NUM)->setParameter('name', 'alpha_num');

        $router->get('/alpha_num_dash/{param}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream($args['param'] . ' | ' . $args['name'])
                );
        })->where('param', Pattern::ALPHA_NUM_DASH)->setParameter('name', 'alpha_num_dash');

        $router->get('/any/{param}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream($args['param'] . ' | ' . $args['name'])
                );
        })->where('param', Pattern::ANY)->setParameter('name', 'any');

        $router->get('/custom/{param}', function ($request, $args) {
            return (new ResponseFactory())
                ->createResponse()
                ->withBody(
                    (new StreamFactory())
                    ->createStream($args['param'] . ' | ' . $args['name'])
                );
        })->where('param', '[\!]{3,5}')->setParameter('name', 'custom');
    }
}
