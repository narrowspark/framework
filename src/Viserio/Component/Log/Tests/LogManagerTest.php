<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\NewRelicHandler;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Monolog\Processor\DebugProcessor;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Log\Exception\RuntimeException;
use Viserio\Component\Log\Event\MessageLoggedEvent;
use Viserio\Component\Log\Logger;
use Viserio\Component\Log\LogManager;
use Viserio\Component\Log\Tests\Fixture\MyCustomLogger;

/**
 * @internal
 */
final class LogManagerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Log\LogManager
     */
    private $manager;

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        @\unlink(__DIR__ . \DIRECTORY_SEPARATOR . 'prod.log');
    }

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
                    'env'      => 'prod',
                    'path'     => __DIR__,
                    'channels' => [
                        'custom_callable' => [
                            'driver'     => 'custom',
                            'via'        => [MyCustomLogger::class, 'handle'],
                            'processors' => [new DebugProcessor()],
                        ],
                        'via_error' => [
                            'driver' => 'custom',
                            'via'    => 'handle',
                        ],
                        'newrelic' => [
                            'driver'    => 'monolog',
                            'channel'   => 'nr',
                            'handler'   => NewRelicHandler::class,
                            'formatter' => 'default',
                        ],
                        'newrelic_html' => [
                            'driver'    => 'monolog',
                            'channel'   => 'nr',
                            'handler'   => NewRelicHandler::class,
                            'formatter' => HtmlFormatter::class,
                        ],
                        'stack' => [
                            'driver'   => 'stack',
                            'channels' => ['daily'],
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

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('prod', $log->getMonolog()->getName());
    }

    public function testDailyLog(): void
    {
        $log = $this->manager->getDriver('daily');

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('prod', $log->getMonolog()->getName());
    }

    public function testEmergencyLog(): void
    {
        $log = $this->manager->getDriver('emergency');

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('narrowspark', $log->getMonolog()->getName());
    }

    public function testSyslogLog(): void
    {
        $log = $this->manager->getDriver('syslog');

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('prod', $log->getMonolog()->getName());
    }

    public function testErrorlogLog(): void
    {
        $log = $this->manager->getDriver('errorlog');

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('prod', $log->getMonolog()->getName());
    }

    public function testSlackLog(): void
    {
        $log = $this->manager->getDriver('slack');

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('prod', $log->getMonolog()->getName());
    }

    public function testStackLog(): void
    {
        $log = $this->manager->getDriver('stack');

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('prod', $log->getMonolog()->getName());
        static::assertCount(1, $log->getMonolog()->getHandlers());
    }

    public function testAggregateLog(): void
    {
        $log = $this->manager->getDriver('aggregate');

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('prod', $log->getMonolog()->getName());
        static::assertCount(2, $log->getMonolog()->getHandlers());
    }

    public function testCreateAEmergencyLoggerIfNoLoggerIsFound(): void
    {
        $log = $this->manager->getDriver('notfound');

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('narrowspark', $log->getMonolog()->getName());
    }

    public function testCustomLoggerWithCallable(): void
    {
        $log       = $this->manager->getDriver('custom_callable');
        $processor = $log->getMonolog()->getProcessors();

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('customCallable', $log->getMonolog()->getName());
        static::assertInstanceOf(DebugProcessor::class, $processor[0]);
    }

    public function testPushProcessorsToMonolog(): void
    {
        $this->manager->pushProcessor(new DebugProcessor());

        $log       = $this->manager->getDriver('single');
        $processor = $log->getMonolog()->getProcessors();

        static::assertInstanceOf(Logger::class, $log);
        static::assertSame('prod', $log->getMonolog()->getName());
        static::assertInstanceOf(DebugProcessor::class, $processor[0]);
    }

    public function testGetChannelAliasForGetDriver(): void
    {
        static::assertEquals($this->manager->getDriver(), $this->manager->getChannel());
        static::assertEquals($this->manager->getDriver('single'), $this->manager->getChannel('single'));
    }

    public function testGetDriversLoggerHasEventManager(): void
    {
        $eventManagerMock = $this->mock(EventManagerContract::class);
        $eventManagerMock->shouldReceive('trigger')
            ->once()
            ->with(\Mockery::type(MessageLoggedEvent::class));

        $this->manager->setEventManager($eventManagerMock);

        $this->manager->log('error', 'test');
    }

    public function testMonologHandlerWithNewRelicHandler(): void
    {
        $this->manager->setContainer(new ArrayContainer([
            NewRelicHandler::class => new NewRelicHandler(),
        ]));

        $log     = $this->manager->getDriver('newrelic');
        $handler = $log->getMonolog()->getHandlers()[0];

        static::assertSame('nr', $log->getMonolog()->getName());
        static::assertCount(1, $log->getMonolog()->getHandlers());
        static::assertInstanceOf(Logger::class, $log);
        static::assertInstanceOf(NewRelicHandler::class, $handler);
        static::assertInstanceOf(NormalizerFormatter::class, $handler->getFormatter());
    }

    public function testMonologHandlerWithNewRelicHandlerAndHtmlFormatter(): void
    {
        $this->manager->setContainer(new ArrayContainer([
            NewRelicHandler::class => new NewRelicHandler(),
            HtmlFormatter::class   => new HtmlFormatter(),
        ]));

        $log     = $this->manager->getDriver('newrelic_html');
        $handler = $log->getMonolog()->getHandlers()[0];

        static::assertSame('nr', $log->getMonolog()->getName());
        static::assertCount(1, $log->getMonolog()->getHandlers());
        static::assertInstanceOf(Logger::class, $log);
        static::assertInstanceOf(NewRelicHandler::class, $handler);
        static::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());
    }

    public function testExceptionOnInvalidVia(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Given custom logger [via_error] could not be resolved.');

        $this->manager->getDriver('via_error');
    }
}
