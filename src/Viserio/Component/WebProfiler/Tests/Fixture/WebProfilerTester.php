<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests\Fixture;

use Viserio\Component\WebProfiler\WebProfiler;

class WebProfilerTester extends WebProfiler
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
