<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Bootstrap;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Cron\Bootstrap\CronScheduling;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;

/**
 * @internal
 */
final class CronSchedulingTest extends TestCase
{
    public function testGetPriority(): void
    {
        static::assertSame(32, CronScheduling::getPriority());
    }

    public function testGetType(): void
    {
        static::assertSame(BootstrapStateContract::TYPE_AFTER, CronScheduling::getType());
    }

    public function testGetBootstrapper(): void
    {
        static::assertSame(LoadServiceProvider::class, CronScheduling::getBootstrapper());
    }
}
