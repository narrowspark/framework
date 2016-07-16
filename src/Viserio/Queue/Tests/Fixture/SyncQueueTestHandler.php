<?php
namespace Viserio\Queue\Tests\Fixture;

class SyncQueueTestHandler
{
    public function run($job, $data)
    {
        $_SERVER['__sync.test'] = func_get_args();
    }
}
