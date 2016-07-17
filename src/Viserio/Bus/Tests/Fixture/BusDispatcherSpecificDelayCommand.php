<?php
namespace Viserio\Bus\Tests\Fixture;

use Viserio\Contracts\Queue\ShouldQueue as ShouldQueueContract;

class BusDispatcherSpecificDelayCommand implements ShouldQueueContract
{
    public $delay = 10;
}
