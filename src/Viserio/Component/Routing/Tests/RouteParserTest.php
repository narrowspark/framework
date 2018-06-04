<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Routing\Pattern;
use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;
use Viserio\Component\Routing\Route\Parser as RouteParser;

/**
 * @internal
 */
final class RouteParserTest extends TestCase
{
    /**
     * @dataProvider routeParsingProvider
     *
     * @param mixed $pattern
     * @param array $conditions
     * @param array $expectedSegments
     */
    public function testRouteParser($pattern, array $conditions, array $expectedSegments): void
    {
        $this->assertEquals($expectedSegments, RouteParser::parse($pattern, $conditions));
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
                [new ParameterMatcher('parameter', '/^(' . Pattern::ANY . ')$/')],
            ],
            [
                '/{param}',
                ['param' => Pattern::ALPHA_NUM],
                [new ParameterMatcher('param', '/^(' . Pattern::ALPHA_NUM . ')$/')],
            ],
            [
                '/user/{id}/profile/{type}',
                ['id' => Pattern::DIGITS, 'type' => Pattern::ALPHA_LOWER],
                [
                    new StaticMatcher('user'),
                    new ParameterMatcher('id', '/^(' . Pattern::DIGITS . ')$/'),
                    new StaticMatcher('profile'),
                    new ParameterMatcher('type', '/^(' . Pattern::ALPHA_LOWER . ')$/'),
                ],
            ],
            [
                '/prefix{param}',
                ['param' => Pattern::ALPHA_NUM],
                [new ParameterMatcher(['param'], '/^prefix(' . Pattern::ALPHA_NUM . ')$/')],
            ],
            [
                '/{param}suffix',
                ['param' => Pattern::ALPHA_NUM],
                [new ParameterMatcher(['param'], '/^(' . Pattern::ALPHA_NUM . ')suffix$/')],
            ],
            [
                '/abc{param1}:{param2}',
                ['param1' => Pattern::ANY, 'param2' => Pattern::ALPHA],
                [new ParameterMatcher(['param1', 'param2'], '/^abc(' . Pattern::ANY . ')\:(' . Pattern::ALPHA . ')$/')],
            ],
            [
                '/shop/{category}:{product}/buy/quantity:{quantity}',
                ['category' => Pattern::ALPHA, 'product' => Pattern::ALPHA, 'quantity' => Pattern::DIGITS],
                [
                    new StaticMatcher('shop'),
                    new ParameterMatcher(['category', 'product'], '/^(' . Pattern::ALPHA . ')\:(' . Pattern::ALPHA . ')$/'),
                    new StaticMatcher('buy'),
                    new ParameterMatcher(['quantity'], '/^quantity\:(' . Pattern::DIGITS . ')$/'),
                ],
            ],
            [
                '/{param:[0-9]+}',
                [],
                [new ParameterMatcher(['param'], '/^([0-9]+)$/')],
            ],
            [
                '/{param:[\:]+}',
                [],
                [new ParameterMatcher(['param'], '/^([\:]+)$/')],
            ],
            [
                // Inline regexps take precedence
                '/{param:[a-z]+}',
                ['param' => Pattern::ALPHA_UPPER],
                [new ParameterMatcher(['param'], '/^([a-z]+)$/')],
            ],
            [
                '/abc{param1:.+}:{param2:.+}',
                [],
                [new ParameterMatcher(['param1', 'param2'], '/^abc(.+)\:(.+)$/')],
            ],
            [
                '/shop/{category:[\w]+}:{product:[\w]+}/buy/quantity:{quantity:[0-9]+}',
                [],
                [
                    new StaticMatcher('shop'),
                    new ParameterMatcher(['category', 'product'], '/^([\w]+)\:([\w]+)$/'),
                    new StaticMatcher('buy'),
                    new ParameterMatcher(['quantity'], '/^quantity\:([0-9]+)$/'),
                ],
            ],
        ];
    }

    /**
     * @dataProvider invalidParsingProvider
     *
     * @param mixed $uri
     */
    public function testInvalidRouteParsing($uri): void
    {
        $this->expectException(\Viserio\Component\Contract\Routing\Exception\InvalidRoutePatternException::class);

        RouteParser::parse($uri, []);
    }

    public function invalidParsingProvider()
    {
        return [
            [
                'abc',
            ],
            [
                '/test/{a/bc}',
            ],
            [
                '/test/{a{bc}',
            ],
            [
                '/test/{abc}}',
            ],
            [
                '/test/{a{bc}}',
            ],
        ];
    }
}
