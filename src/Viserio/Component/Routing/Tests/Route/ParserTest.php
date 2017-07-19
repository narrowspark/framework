<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;
use Viserio\Component\Routing\Route\Parser;

class ParserTest extends TestCase
{
    /**
     * @expectedException \Viserio\Component\Contracts\Routing\Exception\InvalidRoutePatternException
     * @expectedExceptionMessage Invalid route pattern: non-root route must be prefixed with '/', 'test' given.
     */
    public function testParseThrowException(): void
    {
        Parser::parse('test', []);
    }

    public function testParse(): void
    {
        $out = Parser::parse('/user/{id}/create', ['id' => '[0-9]+']);

        self::assertEquals(new StaticMatcher('user'), $out[0]);
        self::assertEquals(new ParameterMatcher('id', '/^([0-9]+)$/'), $out[1]);
        self::assertEquals(new StaticMatcher('create'), $out[2]);
    }

    public function testParseWithDoublePoints(): void
    {
        $out = Parser::parse('/user/{post_slug:[a-z0-9\-]+}/', []);

        self::assertEquals(new StaticMatcher('user'), $out[0]);
        self::assertEquals(new ParameterMatcher('post_slug', '/^([a-z0-9\-]+)$/'), $out[1]);
    }
}
