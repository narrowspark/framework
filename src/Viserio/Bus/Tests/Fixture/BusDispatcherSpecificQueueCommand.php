<?php
declare(strict_types=1);
namespace Viserio\Bus\Tests\Fixture;

use Viserio\Contracts\Queue\ShouldQueue as ShouldQueueContract;

class BusDispatcherSpecificQueueCommand implements ShouldQueueContract
{
    public $queue = 'foo';
}
