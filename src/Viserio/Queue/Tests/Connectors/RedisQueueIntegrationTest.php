<?php
declare(strict_types=1);
namespace Viserio\Queue\Tests\Connectors;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Predis\Client;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Queue\Connectors\RedisQueue;
use Viserio\Queue\Tests\Fixture\RedisQueueIntegrationJob;

class RedisQueueIntegrationTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var \Predis\Client
     */
    private $redis;

    /**
     * @var Viserio\Queue\Connectors\RedisQueue
     */
    private $queue;

    public function setUp()
    {
        if (! getenv('TRAVIS')) {
            $this->markTestSkipped('Redis test runs on travis');
        }

        $this->redis = new Client([
            'servers' => [
                'default' => [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'database' => 5,
                ],
            ],
        ]);
        $this->redis->flushdb();

        $this->queue = new RedisQueue($this->redis);
        $this->queue->setContainer($this->mock(ContainerInterface::class));

        $encrypter = $this->mock(EncrypterContract::class);
        $encrypter->shouldReceive('encrypt');
        $encrypter->shouldReceive('encrypt');

        $this->queue->setEncrypter($encrypter);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->redis->flushdb();
    }

    public function testExpiredJobsArePopped()
    {
        $jobs = [
            new RedisQueueIntegrationJob(0),
            new RedisQueueIntegrationJob(1),
            new RedisQueueIntegrationJob(2),
            new RedisQueueIntegrationJob(3),
        ];

        $this->queue->later(1000, $jobs[0]);
        $this->queue->later(-200, $jobs[1]);
        $this->queue->later(-300, $jobs[2]);
        $this->queue->later(-100, $jobs[3]);

        $this->assertEquals($jobs[2], unserialize(json_decode($this->queue->pop()->getRawBody())->data->command64));
        $this->assertEquals($jobs[1], unserialize(json_decode($this->queue->pop()->getRawBody())->data->command64));
        $this->assertEquals($jobs[3], unserialize(json_decode($this->queue->pop()->getRawBody())->data->command64));
        $this->assertNull($this->queue->pop());
        $this->assertEquals(1, $this->redis->connection()->zcard('queues:default:delayed'));
        $this->assertEquals(3, $this->redis->connection()->zcard('queues:default:reserved'));
    }
}
