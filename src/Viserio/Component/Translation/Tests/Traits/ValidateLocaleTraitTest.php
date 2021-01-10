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

namespace Viserio\Component\Translation\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Traits\ValidateLocaleTrait;
use Viserio\Contract\Translation\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ValidateLocaleTraitTest extends TestCase
{
    use ValidateLocaleTrait;

    /**
     * @dataProvider provideAssertValidLocaleToThrowExceptionCases
     */
    public function testAssertValidLocaleToThrowException($locale): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::assertValidLocale($locale);
    }

    public static function provideAssertValidLocaleToThrowExceptionCases(): iterable
    {
        return [
            ['?0'],
            [')(/'],
            ['/&_'],
        ];
    }
}
