<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Fixture;

use Exception;

class FailingSyncQueueHandler
{
    public function run($job, $data)
    {
        throw new Exception();
    }

    public function failed()
    {
        $_SERVER['__sync.failed'] = true;
    }
}
