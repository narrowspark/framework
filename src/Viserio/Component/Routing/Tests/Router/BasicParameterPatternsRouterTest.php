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
use Viserio\Contract\Routing\Pattern;
use Viserio\Contract\Routing\Router as RouterContract;

/**
 * @internal
 *
 * @small
 */
final class BasicParameterPatternsRouterTest extends AbstractRouterBaseTest
{
    use TestRouter404Trait;

    /**
     * @return array
     */
    public static function provideRouterCases(): array
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

    public static function provideRouter404Cases(): iterable
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

    protected function definitions(RouterContract $router): void
    {
        $router->get('/digits/{param}', function ($request, $param, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($param . ' | ' . $name)
                );
        })->where('param', Pattern::DIGITS)->addParameter('name', 'digits');

        $router->get('/alpha/{param}', function ($request, $param, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($param . ' | ' . $name)
                );
        })->where('param', Pattern::ALPHA)->addParameter('name', 'alpha');

        $router->get('/alpha_low/{param}', function ($request, $param, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($param . ' | ' . $name)
                );
        })->where('param', Pattern::ALPHA_LOWER)->addParameter('name', 'alpha_low');

        $router->get('/alpha_up/{param}', function ($request, $param, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($param . ' | ' . $name)
                );
        })->where('param', Pattern::ALPHA_UPPER)->addParameter('name', 'alpha_up');

        $router->get('/alpha_num/{param}', function ($request, $param, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($param . ' | ' . $name)
                );
        })->where('param', Pattern::ALPHA_NUM)->addParameter('name', 'alpha_num');

        $router->get('/alpha_num_dash/{param}', function ($request, $param, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($param . ' | ' . $name)
                );
        })->where('param', Pattern::ALPHA_NUM_DASH)->addParameter('name', 'alpha_num_dash');

        $router->get('/any/{param}', function ($request, $param, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($param . ' | ' . $name)
                );
        })->where('param', Pattern::ANY)->addParameter('name', 'any');

        $router->get('/custom/{param}', function ($request, $param, $name) {
            return $this->responseFactory
                ->createResponse()
                ->withBody(
                    $this->streamFactory->createStream($param . ' | ' . $name)
                );
        })->where('param', '[\!]{3,5}')->addParameter('name', 'custom');
    }
}
