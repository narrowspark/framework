<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests;

use RuntimeException;
use Viserio\Contracts\Routing\Exceptions\InvalidRoutePatternException;
use Viserio\Contracts\Routing\Pattern;
use Viserio\Routing\Matchers\StaticMatcher;
use Viserio\Routing\RouteParser;
use Viserio\Routing\Segments\ParameterSegment;

class RouteParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider routeParsingProvider
     *
     * @param mixed $pattern
     * @param array $conditions
     * @param array $expectedSegments
     */
    public function testRouteParser($pattern, array $conditions, array $expectedSegments)
    {
        $parser = new RouteParser();

        self::assertEquals($expectedSegments, $parser->parse($pattern, $conditions));
    }

    public function routeParsingProvider()
    {
        return [
            [
                // Empty route
                '',
                [],
                [],
            ],
            [
                // Empty route
                '/',
                [],
                [new StaticMatcher('')],
            ],
            [
                '/user',
                [],
                [new StaticMatcher('user')],
            ],
            [
                '/user/',
                [],
                [new StaticMatcher('user'), new StaticMatcher('')],
            ],
            [
                '/user/profile',
                [],
                [new StaticMatcher('user'), new StaticMatcher('profile')],
            ],
            [
                '/{parameter}',
                [],
                [new ParameterSegment('parameter', '/^(' . Pattern::ANY . ')$/')],
            ],
            [
                '/{param}',
                ['param' => Pattern::ALPHA_NUM],
                [new ParameterSegment('param', '/^(' . Pattern::ALPHA_NUM . ')$/')],
            ],
            [
                '/user/{id}/profile/{type}',
                ['id' => Pattern::DIGITS, 'type' => Pattern::ALPHA_LOWER],
                [
                    new StaticMatcher('user'),
                    new ParameterSegment('id', '/^(' . Pattern::DIGITS . ')$/'),
                    new StaticMatcher('profile'),
                    new ParameterSegment('type', '/^(' . Pattern::ALPHA_LOWER . ')$/'),
                ],
            ],
            [
                '/prefix{param}',
                ['param' => Pattern::ALPHA_NUM],
                [new ParameterSegment(['param'], '/^prefix(' . Pattern::ALPHA_NUM . ')$/')],
            ],
            [
                '/{param}suffix',
                ['param' => Pattern::ALPHA_NUM],
                [new ParameterSegment(['param'], '/^(' . Pattern::ALPHA_NUM . ')suffix$/')],
            ],
            [
                '/abc{param1}:{param2}',
                ['param1' => Pattern::ANY, 'param2' => Pattern::ALPHA],
                [new ParameterSegment(['param1', 'param2'], '/^abc(' . Pattern::ANY . ')\:(' . Pattern::ALPHA . ')$/')],
            ],
            [
                '/shop/{category}:{product}/buy/quantity:{quantity}',
                ['category' => Pattern::ALPHA, 'product' => Pattern::ALPHA, 'quantity' => Pattern::DIGITS],
                [
                    new StaticMatcher('shop'),
                    new ParameterSegment(['category', 'product'], '/^(' . Pattern::ALPHA . ')\:(' . Pattern::ALPHA . ')$/'),
                    new StaticMatcher('buy'),
                    new ParameterSegment(['quantity'], '/^quantity\:(' . Pattern::DIGITS . ')$/'),
                ],
            ],
            [
                '/{param:[0-9]+}',
                [],
                [new ParameterSegment(['param'], '/^([0-9]+)$/')],
            ],
            [
                '/{param:[\:]+}',
                [],
                [new ParameterSegment(['param'], '/^([\:]+)$/')],
            ],
            [
                // Inline regexps take precedence
                '/{param:[a-z]+}',
                ['param' => Pattern::ALPHA_UPPER],
                [new ParameterSegment(['param'], '/^([a-z]+)$/')],
            ],
            [
                '/abc{param1:.+}:{param2:.+}',
                [],
                [new ParameterSegment(['param1', 'param2'], '/^abc(.+)\:(.+)$/')],
            ],
            [
                '/shop/{category:[\w]+}:{product:[\w]+}/buy/quantity:{quantity:[0-9]+}',
                [],
                [
                    new StaticMatcher('shop'),
                    new ParameterSegment(['category', 'product'], '/^([\w]+)\:([\w]+)$/'),
                    new StaticMatcher('buy'),
                    new ParameterSegment(['quantity'], '/^quantity\:([0-9]+)$/'),
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidParsingProvider
     *
     * @param mixed $uri
     * @param mixed $expectedExceptionType
     */
    public function testInvalidRouteParsing($uri, $expectedExceptionType)
    {
        $this->setExpectedExceptionRegExp(
            $expectedExceptionType ?: RuntimeException::class,
            '/.*/'
        );

        (new RouteParser())->parse($uri, []);
    }

    public function invalidParsingProvider()
    {
        return [
            [
                'abc',
                InvalidRoutePatternException::class,
            ],
            [
                '/test/{a/bc}',
                InvalidRoutePatternException::class,
            ],
            [
                '/test/{a{bc}',
                InvalidRoutePatternException::class,
            ],
            [
                '/test/{abc}}',
                InvalidRoutePatternException::class,
            ],
            [
                '/test/{a{bc}}',
                InvalidRoutePatternException::class,
            ],
        ];
    }
}
