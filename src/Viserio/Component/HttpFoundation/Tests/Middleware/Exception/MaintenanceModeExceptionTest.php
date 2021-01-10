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

namespace Viserio\Component\HttpFoundation\Tests\Middleware\Exception;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Viserio\Component\HttpFoundation\Exception\MaintenanceModeException;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
