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

namespace Viserio\Component\Translation\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Traits\ValidateLocaleTrait;
use Viserio\Contract\Translation\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class ValidateLocaleTraitTest extends TestCase
{
    use ValidateLocaleTrait;

    /**
     * @dataProvider provideAssertValidLocaleToThrowExceptionCases
     *
     * @param mixed $locale
     */
    public function testAssertValidLocaleToThrowException($locale): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::assertValidLocale($locale);
    }

    public function provideAssertValidLocaleToThrowExceptionCases(): iterable
    {
        return [
            ['?0'],
            [')(/'],
            ['/&_'],
        ];
    }
}
