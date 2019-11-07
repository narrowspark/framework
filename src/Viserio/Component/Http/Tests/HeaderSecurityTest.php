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
 */
final class HeaderSecurityTest extends TestCase
{
    /**
     * Data for filter value.
     *
     * @return iterable
     */
    public function provideFiltersValuesPerRfc7230Cases(): iterable
    {
        return [
            ["This is a\n test", 'This is a test'],
            ["This is a\r test", 'This is a test'],
            ["This is a\n\r test", 'This is a test'],
            ["This is a\r\n  test", "This is a\r\n  test"],
            ["This is a \r\ntest", 'This is a test'],
            ["This is a \r\n\n test", 'This is a  test'],
            ["This is a\n\n test", 'This is a test'],
            ["This is a\r\r test", 'This is a test'],
            ["This is a \r\r\n test", "This is a \r\n test"],
            ["This is a \r\n\r\ntest", 'This is a test'],
            ["This is a \r\n\n\r\n test", "This is a \r\n test"],
        ];
    }

    /**
     * @dataProvider provideFiltersValuesPerRfc7230Cases
     * @group ZF2015-04
     *
     * @param mixed $value
     * @param mixed $expected
     */
    public function testFiltersValuesPerRfc7230($value, $expected): void
    {
        self::assertEquals($expected, HeaderSecurity::filter($value));
    }

    public function provideValidatesValuesPerRfc7230Cases(): iterable
    {
        return [
            ["This is a\n test", 'assertFalse'],
            ["This is a\r test", 'assertFalse'],
            ["This is a\n\r test", 'assertFalse'],
            ["This is a\r\n  test", 'assertTrue'],
            ["This is a \r\ntest", 'assertFalse'],
            ["This is a \r\n\n test", 'assertFalse'],
            ["This is a\n\n test", 'assertFalse'],
            ["This is a\r\r test", 'assertFalse'],
            ["This is a \r\r\n test", 'assertFalse'],
            ["This is a \r\n\r\ntest", 'assertFalse'],
            ["This is a \r\n\n\r\n test", 'assertFalse'],
            ["This is a \xFF test", 'assertFalse'],
            ["This is a \x7F test", 'assertFalse'],
            ["This is a \x7E test", 'assertTrue'],
        ];
    }

    /**
     * @dataProvider provideValidatesValuesPerRfc7230Cases
     * @group ZF2015-04
     *
     * @param mixed $value
     * @param mixed $assertion
     */
    public function testValidatesValuesPerRfc7230($value, $assertion): void
    {
        $this->{$assertion}(HeaderSecurity::isValid($value));
    }

    public function provideAssertValidRaisesExceptionForInvalidValueCases(): iterable
    {
        return [
            ["This is a\n test"],
            ["This is a\r test"],
            ["This is a\n\r test"],
            ["This is a \r\ntest"],
            ["This is a \r\n\n test"],
            ["This is a\n\n test"],
            ["This is a\r\r test"],
            ["This is a \r\r\n test"],
            ["This is a \r\n\r\ntest"],
            ["This is a \r\n\n\r\n test"],
        ];
    }

    /**
     * @dataProvider provideAssertValidRaisesExceptionForInvalidValueCases
     * @group ZF2015-04
     *
     * @param mixed $value
     */
    public function testAssertValidRaisesExceptionForInvalidValue($value): void
    {
        $this->expectException(InvalidArgumentException::class);

        HeaderSecurity::assertValid($value);
    }
}
