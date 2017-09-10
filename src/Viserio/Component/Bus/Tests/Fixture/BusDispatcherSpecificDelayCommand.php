<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests\Fixture;

use Viserio\Component\Contract\Queue\ShouldQueue as ShouldQueueContract;

class BusDispatcherSpecificDelayCommand implements ShouldQueueContract
{
    public $delay = 10;
}
