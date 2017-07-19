<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Fixture;

class SyncQueueHandler
{
    public function run($job, $data): void
    {
        $_SERVER['__sync.test'] = \func_get_args();
    }
}
