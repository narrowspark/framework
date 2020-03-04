<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Http\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\HeaderSecurity;

/**
 * Tests for Zend\Diactoros\HeaderSecurity.
 *
 * Tests are largely derived from those for Zend\Http\Header\HeaderValue in
 * Zend Framework, released with the copyright and license below.
 *
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 *
 * @internal
 *
 * @small
 * @coversNothing
 */
final class HeaderSecurityTest extends TestCase
{
    /**
     * Data for filter value.
     *
     * @return iterable<array<int, string>>
     */
    public static function provideFiltersValuesPerRfc7230Cases(): iterable
    {
        yield ["This is a\n test", 'This is a test'];

        yield ["This is a\r test", 'This is a test'];

        yield ["This is a\n\r test", 'This is a test'];

        yield ["This is a\r\n  test", "This is a\r\n  test"];

        yield ["This is a \r\ntest", 'This is a test'];

        yield ["This is a \r\n\n test", 'This is a  test'];

        yield ["This is a\n\n test", 'This is a test'];

        yield ["This is a\r\r test", 'This is a test'];

        yield ["This is a \r\r\n test", "This is a \r\n test"];

        yield ["This is a \r\n\r\ntest", 'This is a test'];

        yield ["This is a \r\n\n\r\n test", "This is a \r\n test"];
    }

    /**
     * @dataProvider provideFiltersValuesPerRfc7230Cases
     */
    public function testFiltersValuesPerRfc7230(string $value, string $expected): void
    {
        self::assertEquals($expected, HeaderSecurity::filter($value));
    }

    /**
     * @return iterable<array<int, bool|string>>
     */
    public static function provideValidatesValuesPerRfc7230Cases(): iterable
    {
        yield ["This is a\n test", false];

        yield ["This is a\r test", false];

        yield ["This is a\n\r test", false];

        yield ["This is a\r\n  test", true];

        yield ["This is a \r\ntest", false];

        yield ["This is a \r\n\n test", false];

        yield ["This is a\n\n test", false];

        yield ["This is a\r\r test", false];

        yield ["This is a \r\r\n test", false];

        yield ["This is a \r\n\r\ntest", false];

        yield ["This is a \r\n\n\r\n test", false];

        yield ["This is a \xFF test", false];

        yield ["This is a \x7F test", false];

        yield ["This is a \x7E test", true];
    }

    /**
     * @dataProvider provideValidatesValuesPerRfc7230Cases
     */
    public function testValidatesValuesPerRfc7230(string $value, bool $assertion): void
    {
        self::assertSame(HeaderSecurity::isValid($value), $assertion);
    }

    /**
     * @return iterable<array<int, string>>
     */
    public static function provideAssertValidRaisesExceptionForInvalidValueCases(): iterable
    {
        yield ["This is a\n test"];

        yield ["This is a\r test"];

        yield ["This is a\n\r test"];

        yield ["This is a \r\ntest"];

        yield ["This is a \r\n\n test"];

        yield ["This is a\n\n test"];

        yield ["This is a\r\r test"];

        yield ["This is a \r\r\n test"];

        yield ["This is a \r\n\r\ntest"];

        yield ["This is a \r\n\n\r\n test"];
    }

    /**
     * @dataProvider provideAssertValidRaisesExceptionForInvalidValueCases
     */
    public function testAssertValidRaisesExceptionForInvalidValue(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        HeaderSecurity::assertValid($value);
    }
}
