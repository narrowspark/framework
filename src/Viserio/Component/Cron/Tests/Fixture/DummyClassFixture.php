<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Fixture;

use Viserio\Component\Cron\Schedule;

class DummyClassFixture
{
    protected $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }
}
