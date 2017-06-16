<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Http\Middlewares\Exception;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Foundation\Http\Exception\MaintenanceModeException;

class MaintenanceModeExceptionTest extends TestCase
{
    public function testExceptionFunctions()
    {
        $time       = 1491737578;
        $retryAfter = 5;

        $exception = new MaintenanceModeException($time, $retryAfter, 'test');

        self::assertEquals(Chronos::createFromTimestamp($time)->addSeconds($retryAfter), $exception->getWillBeAvailableAt());
        self::assertEquals(Chronos::createFromTimestamp($time), $exception->getWentDownAt());
        self::assertSame(5, $exception->getRetryAfter());
    }
}
