<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Connectors;

use Defuse\Crypto\Key;
use Exception;
use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Predis\Client;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Queue\Connectors\RedisQueue;
use Viserio\Component\Queue\Tests\Fixture\RedisQueueIntegrationJob;

class RedisQueueIntegrationTest extends MockeryTestCase
{
    /**
     * @var \Predis\Client
     */
    private $redis;

    /**
     * @var \Viserio\Component\Queue\Connectors\RedisQueue
     */
    private $queue;

    /**
     * @var \Viserio\Component\Encryption\Encrypter
     */
    private $encrypter;

    public function setUp()
    {
        $this->redis = new Client([
            'servers' => [
                'default' => [
                    'host'     => '127.0.0.1',
                    'port'     => 6379,
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

        $this->encrypter = new Encrypter(Key::createNewRandomKey()->saveToAsciiSafeString());

        $this->queue->setEncrypter($this->encrypter);
    }

    public function tearDown()
    {
        $this->redis->flushdb();

        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);
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

        self::assertEquals($jobs[2], unserialize(base64_decode($this->encrypter->decrypt(json_decode($this->queue->pop()->getRawBody())->data->command64))));
        self::assertEquals($jobs[1], unserialize(base64_decode($this->encrypter->decrypt(json_decode($this->queue->pop()->getRawBody())->data->command64))));
        self::assertEquals($jobs[3], unserialize(base64_decode($this->encrypter->decrypt(json_decode($this->queue->pop()->getRawBody())->data->command64))));
        self::assertNull($this->queue->pop());
        self::assertEquals(1, $this->redis->zcard('queues:default:delayed'));
        self::assertEquals(3, $this->redis->zcard('queues:default:reserved'));
    }
}
