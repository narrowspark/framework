<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\Fixture;

use Viserio\Component\Profiler\Profiler;

class ProfilerTester extends Profiler
{
    /**
     * Determine if we are running in the console.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function runningInConsole(): bool
    {
        return false;
    }
}
