<?php
declare(strict_types=1);
namespace Viserio\Queue\Tests\Connectors;

use Defuse\Crypto\Key;
use Exception;
use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Predis\Client;
use Viserio\Encryption\Encrypter;
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
     * @var \Viserio\Queue\Connectors\RedisQueue
     */
    private $queue;

    /**
     * @var \Viserio\Encryption\Encrypter
     */
    private $encrypter;

    public function setUp()
    {
        $this->redis = new Client([
            'servers' => [
                'default' => [
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'database' => 5,
                ],
            ],
        ]);

        try {
            $this->redis->ping();
        } catch (Exception $exception) {
            $this->markTestSkipped('Test is only tested if redis is running.');
        }

        $this->redis->flushdb();

        $this->queue = new RedisQueue($this->redis);
        $this->queue->setContainer($this->mock(ContainerInterface::class));

        $this->encrypter = new Encrypter(Key::createNewRandomKey());

        $this->queue->setEncrypter($this->encrypter);
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

        $this->assertEquals($jobs[2], unserialize(base64_decode($this->encrypter->decrypt(json_decode($this->queue->pop()->getRawBody())->data->command64))));
        $this->assertEquals($jobs[1], unserialize(base64_decode($this->encrypter->decrypt(json_decode($this->queue->pop()->getRawBody())->data->command64))));
        $this->assertEquals($jobs[3], unserialize(base64_decode($this->encrypter->decrypt(json_decode($this->queue->pop()->getRawBody())->data->command64))));
        $this->assertNull($this->queue->pop());
        $this->assertEquals(1, $this->redis->zcard('queues:default:delayed'));
        $this->assertEquals(3, $this->redis->zcard('queues:default:reserved'));
    }
}
