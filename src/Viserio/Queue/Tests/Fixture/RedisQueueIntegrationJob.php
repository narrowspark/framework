<?php
declare(strict_types=1);
namespace Viserio\Queue\Tests\Fixture;

class RedisQueueIntegrationJob
{
    public $i;

    public function __construct($i)
    {
        $this->i = $i;
    }

    public function handle()
    {
        //
    }
}
