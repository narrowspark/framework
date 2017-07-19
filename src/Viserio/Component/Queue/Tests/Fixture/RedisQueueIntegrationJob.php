<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Fixture;

class RedisQueueIntegrationJob
{
    public $i;

    public function __construct($i)
    {
        $this->i = $i;
    }

    public function handle(): void
    {
    }
}
