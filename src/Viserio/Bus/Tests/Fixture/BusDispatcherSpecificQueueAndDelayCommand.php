<?php

declare(strict_types=1);
namespace Viserio\Bus\Tests\Fixture;

use Viserio\Contracts\Queue\ShouldQueue as ShouldQueueContract;

class BusDispatcherSpecificQueueAndDelayCommand implements ShouldQueueContract
{
    public $queue = 'foo';

    public $delay = 10;
}
