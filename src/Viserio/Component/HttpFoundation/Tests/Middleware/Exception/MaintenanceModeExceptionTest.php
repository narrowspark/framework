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

namespace Viserio\Component\HttpFoundation\Tests\Middleware\Exception;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Viserio\Component\HttpFoundation\Exception\MaintenanceModeException;

/**
 * @internal
 *
 * @small
 */
final class MaintenanceModeExceptionTest extends TestCase
{
    public function testExceptionFunctions(): void
    {
        $time = 1491737578;
        $retryAfter = 5;

        $exception = new MaintenanceModeException($time, $retryAfter, 'test');

        self::assertEquals(Chronos::createFromTimestamp($time)->addSeconds($retryAfter), $exception->getWillBeAvailableAt());
        self::assertEquals(Chronos::createFromTimestamp($time), $exception->getWentDownAt());
        self::assertSame(5, $exception->getRetryAfter());
    }
}
