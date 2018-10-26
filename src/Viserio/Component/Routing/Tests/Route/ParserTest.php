<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;
use Viserio\Component\Routing\Route\Parser;

/**
 * @internal
 */
final class ParserTest extends TestCase
{
    public function testParseThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Routing\Exception\InvalidRoutePatternException::class);
        $this->expectExceptionMessage('Invalid route pattern: non-root route must be prefixed with \'/\', \'test\' given.');

        Parser::parse('test', []);
    }

    public function testParse(): void
    {
        $out = Parser::parse('/user/{id}/create', ['id' => '[0-9]+']);

        $this->assertEquals(new StaticMatcher('user'), $out[0]);
        $this->assertEquals(new ParameterMatcher('id', '/^([0-9]+)$/'), $out[1]);
        $this->assertEquals(new StaticMatcher('create'), $out[2]);
    }

    public function testParseWithDoublePoints(): void
    {
        $out = Parser::parse('/user/{post_slug:[a-z0-9\-]+}/', []);

        $this->assertEquals(new StaticMatcher('user'), $out[0]);
        $this->assertEquals(new ParameterMatcher('post_slug', '/^([a-z0-9\-]+)$/'), $out[1]);
    }
}
