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

namespace Viserio\Component\Exception\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Traits\DetermineErrorLevelTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class DetermineErrorLevelTraitTest extends TestCase
{
    use DetermineErrorLevelTrait;

    public function testIsLevelFatal(): void
    {
        self::assertFalse(self::isLevelFatal(\E_DEPRECATED));
        self::assertTrue(self::isLevelFatal(\E_ERROR));
        self::assertTrue(self::isLevelFatal(\E_PARSE));
        self::assertTrue(self::isLevelFatal(\E_CORE_ERROR));
        self::assertTrue(self::isLevelFatal(\E_CORE_WARNING));
        self::assertTrue(self::isLevelFatal(\E_COMPILE_ERROR));
        self::assertTrue(self::isLevelFatal(\E_COMPILE_WARNING));
    }
}
