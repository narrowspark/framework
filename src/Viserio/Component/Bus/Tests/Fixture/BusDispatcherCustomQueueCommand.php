<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests\Fixture;

use Viserio\Component\Contracts\Queue\ShouldQueue as ShouldQueueContract;

class BusDispatcherCustomQueueCommand implements ShouldQueueContract
{
    public function queue($queue, $command)
    {
        $queue->push($command);
    }
}
