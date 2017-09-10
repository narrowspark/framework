<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests\Fixture;

use Viserio\Component\Contract\Queue\ShouldQueue as ShouldQueueContract;

class BusDispatcherSpecificQueueAndDelayCommand implements ShouldQueueContract
{
    public $queue = 'foo';

    public $delay = 10;
}
