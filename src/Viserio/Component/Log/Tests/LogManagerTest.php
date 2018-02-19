<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Log\Logger;
use Viserio\Component\Log\LogManager;
use Viserio\Component\Log\Tests\Fixture\MyCustomLogger;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class LogManagerTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Viserio\Component\Log\LogManager
     */
    private $manager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'viserio' => [
                'logging' => [
                    'name'     => 'narrowspark',
                    'path'     => __DIR__,
                    'channels' => [
                        'aggregate' => [
                            'driver'   => 'aggregate',
                            'channels' => ['single', 'daily'],
                        ],
                        'single' => [
                            'driver' => 'single',
                            'level'  => 'debug',
                        ],
                        'daily' => [
                            'driver' => 'daily',
                            'level'  => 'debug',
                            'days'   => 3,
                        ],
                        'syslog' => [
                            'driver' => 'syslog',
                            'level'  => 'debug',
                        ],
                        'errorlog' => [
                            'driver' => 'errorlog',
                            'level'  => 'debug',
                        ],
                        'custom_callable' => [
                            'driver' => 'custom',
                            'via' => [MyCustomLogger::class, 'handle'],
                        ],
                    ],
                ],
            ],
        ];

        $this->manager = new LogManager($config);
    }

    public function testSingleLog(): void
    {
        $log = $this->manager->getDriver('single');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('production', $log->getMonolog()->getName());
    }

    public function testDailyLog(): void
    {
        $log = $this->manager->getDriver('daily');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('production', $log->getMonolog()->getName());
    }

    public function testEmergencyLog(): void
    {
        $log = $this->manager->getDriver('emergency');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('narrowspark', $log->getMonolog()->getName());
    }

    public function testSyslogLog(): void
    {
        $log = $this->manager->getDriver('syslog');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('production', $log->getMonolog()->getName());
    }

    public function testErrorlogLog(): void
    {
        $log = $this->manager->getDriver('errorlog');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('production', $log->getMonolog()->getName());
    }

    public function testSlackLog(): void
    {
        $log = $this->manager->getDriver('slack');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('production', $log->getMonolog()->getName());
    }

    public function testAggregateLog(): void
    {
        $log = $this->manager->getDriver('aggregate');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('production', $log->getMonolog()->getName());
        self::assertCount(2, $log->getMonolog()->getHandlers());
    }

    public function testCreateAEmergencyLoggerIfNoLoggerIsFound(): void
    {
        $log = $this->manager->getDriver('notfound');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('narrowspark', $log->getMonolog()->getName());
    }

    public function testCustomLoggerWithCallable()
    {
        $log = $this->manager->getDriver('custom_callable');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('customCallable', $log->getMonolog()->getName());
    }
}
