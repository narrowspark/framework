<?php

declare(strict_types=1);
namespace Viserio\Queue\Tests\Fixture;

class SyncQueueHandler
{
    public function run($job, $data)
    {
        $_SERVER['__sync.test'] = func_get_args();
    }
}
