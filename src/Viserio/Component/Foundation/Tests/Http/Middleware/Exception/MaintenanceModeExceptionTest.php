<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Http\Middleware\Exception;

use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Foundation\Http\Exception\MaintenanceModeException;

/**
 * @internal
 */
final class MaintenanceModeExceptionTest extends TestCase
{
    public function testExceptionFunctions(): void
    {
        $time       = 1491737578;
        $retryAfter = 5;

        $exception = new MaintenanceModeException($time, $retryAfter, 'test');

        $this->assertEquals(Chronos::createFromTimestamp($time)->addSeconds($retryAfter), $exception->getWillBeAvailableAt());
        $this->assertEquals(Chronos::createFromTimestamp($time), $exception->getWentDownAt());
        $this->assertSame(5, $exception->getRetryAfter());
    }
}
