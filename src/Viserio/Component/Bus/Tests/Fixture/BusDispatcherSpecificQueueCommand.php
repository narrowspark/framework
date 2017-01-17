<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests\Fixture;

use Viserio\Component\Contracts\Queue\ShouldQueue as ShouldQueueContract;

class BusDispatcherSpecificQueueCommand implements ShouldQueueContract
{
    public $queue = 'foo';
}
