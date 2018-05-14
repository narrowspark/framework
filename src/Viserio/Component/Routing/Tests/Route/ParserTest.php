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

namespace Viserio\Component\Routing\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;
use Viserio\Component\Routing\Route\Parser;

/**
 * @internal
 *
 * @small
 */
final class ParserTest extends TestCase
{
    public function testParseThrowException(): void
    {
        $this->expectException(\Viserio\Contract\Routing\Exception\InvalidRoutePatternException::class);
        $this->expectExceptionMessage('Invalid route pattern: non-root route must be prefixed with \'/\', \'test\' given.');

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
