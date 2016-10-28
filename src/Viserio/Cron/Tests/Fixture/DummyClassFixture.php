<?php
declare(strict_types=1);
namespace Viserio\Cron\Tests\Fixture;

use Viserio\Cron\Schedule;

class DummyClassFixture
{
    protected $schedule;

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }
}
