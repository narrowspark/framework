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

namespace Viserio\Component\Exception\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Traits\DetermineErrorLevelTrait;

/**
 * @internal
 *
 * @small
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
