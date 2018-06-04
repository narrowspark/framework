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
use Viserio\Component\Log\Event\MessageLoggedEvent;
use Viserio\Component\Log\Logger;
use Viserio\Component\Log\LogManager;
use Viserio\Component\Log\Tests\Fixture\MyCustomLogger;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class LogManagerTest extends MockeryTestCase
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
                    ],
                ],
            ],
        ];

        $this->manager = new LogManager($config);
    }

    public function testSingleLog(): void
    {
        $log = $this->manager->getDriver('single');

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('prod', $log->getMonolog()->getName());
    }

    public function testDailyLog(): void
    {
        $log = $this->manager->getDriver('daily');

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('prod', $log->getMonolog()->getName());
    }

    public function testEmergencyLog(): void
    {
        $log = $this->manager->getDriver('emergency');

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('narrowspark', $log->getMonolog()->getName());
    }

    public function testSyslogLog(): void
    {
        $log = $this->manager->getDriver('syslog');

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('prod', $log->getMonolog()->getName());
    }

    public function testErrorlogLog(): void
    {
        $log = $this->manager->getDriver('errorlog');

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('prod', $log->getMonolog()->getName());
    }

    public function testSlackLog(): void
    {
        $log = $this->manager->getDriver('slack');

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('prod', $log->getMonolog()->getName());
    }

    public function testAggregateLog(): void
    {
        $log = $this->manager->getDriver('aggregate');

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('prod', $log->getMonolog()->getName());
        $this->assertCount(2, $log->getMonolog()->getHandlers());
    }

    public function testCreateAEmergencyLoggerIfNoLoggerIsFound(): void
    {
        $log = $this->manager->getDriver('notfound');

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('narrowspark', $log->getMonolog()->getName());
    }

    public function testCustomLoggerWithCallable(): void
    {
        $log       = $this->manager->getDriver('custom_callable');
        $processor = $log->getMonolog()->getProcessors();

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('customCallable', $log->getMonolog()->getName());
        $this->assertInstanceOf(DebugProcessor::class, $processor[0]);
    }

    public function testPushProcessorsToMonolog(): void
    {
        $this->manager->pushProcessor(new DebugProcessor());

        $log       = $this->manager->getDriver('single');
        $processor = $log->getMonolog()->getProcessors();

        $this->assertInstanceOf(Logger::class, $log);
        $this->assertSame('prod', $log->getMonolog()->getName());
        $this->assertInstanceOf(DebugProcessor::class, $processor[0]);
    }

    public function testGetChannelAliasForGetDriver(): void
    {
        $this->assertEquals($this->manager->getDriver(), $this->manager->getChannel());
        $this->assertEquals($this->manager->getDriver('single'), $this->manager->getChannel('single'));
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

        $this->assertSame('nr', $log->getMonolog()->getName());
        $this->assertCount(1, $log->getMonolog()->getHandlers());
        $this->assertInstanceOf(Logger::class, $log);
        $this->assertInstanceOf(NewRelicHandler::class, $handler);
        $this->assertInstanceOf(NormalizerFormatter::class, $handler->getFormatter());
    }

    public function testMonologHandlerWithNewRelicHandlerAndHtmlFormatter(): void
    {
        $this->manager->setContainer(new ArrayContainer([
            NewRelicHandler::class => new NewRelicHandler(),
            HtmlFormatter::class   => new HtmlFormatter(),
        ]));

        $log     = $this->manager->getDriver('newrelic_html');
        $handler = $log->getMonolog()->getHandlers()[0];

        $this->assertSame('nr', $log->getMonolog()->getName());
        $this->assertCount(1, $log->getMonolog()->getHandlers());
        $this->assertInstanceOf(Logger::class, $log);
        $this->assertInstanceOf(NewRelicHandler::class, $handler);
        $this->assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());
    }

    public function testExceptionOnInvalidVia(): void
    {
        $this->expectException(\Viserio\Component\Contract\Log\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Given custom logger [via_error] could not be resolved.');

        $this->manager->getDriver('via_error');
    }
}
