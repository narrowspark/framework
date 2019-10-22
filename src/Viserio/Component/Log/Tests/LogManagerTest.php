<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Log\Tests;

use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\NewRelicHandler;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Monolog\Processor\DebugProcessor;
use Viserio\Component\Log\Event\MessageLoggedEvent;
use Viserio\Component\Log\Logger;
use Viserio\Component\Log\LogManager;
use Viserio\Component\Log\Tests\Fixture\MyCustomLogger;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Log\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 */
final class LogManagerTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Log\LogManager */
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
                    'name' => 'narrowspark',
                    'env' => 'prod',
                    'path' => __DIR__,
                    'channels' => [
                        'custom_callable' => [
                            'driver' => 'custom',
                            'via' => [MyCustomLogger::class, 'handle'],
                            'processors' => [new DebugProcessor()],
                        ],
                        'via_error' => [
                            'driver' => 'custom',
                            'via' => 'handle',
                        ],
                        'newrelic' => [
                            'driver' => 'monolog',
                            'channel' => 'nr',
                            'handler' => NewRelicHandler::class,
                            'formatter' => 'default',
                        ],
                        'newrelic_html' => [
                            'driver' => 'monolog',
                            'channel' => 'nr',
                            'handler' => NewRelicHandler::class,
                            'formatter' => HtmlFormatter::class,
                        ],
                        'stack' => [
                            'driver' => 'stack',
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

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('prod', $log->getMonolog()->getName());
    }

    public function testDailyLog(): void
    {
        $log = $this->manager->getDriver('daily');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('prod', $log->getMonolog()->getName());
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
        self::assertSame('prod', $log->getMonolog()->getName());
    }

    public function testErrorlogLog(): void
    {
        $log = $this->manager->getDriver('errorlog');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('prod', $log->getMonolog()->getName());
    }

    public function testSlackLog(): void
    {
        $log = $this->manager->getDriver('slack');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('prod', $log->getMonolog()->getName());
    }

    public function testStackLog(): void
    {
        $log = $this->manager->getDriver('stack');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('prod', $log->getMonolog()->getName());
        self::assertCount(1, $log->getMonolog()->getHandlers());
    }

    public function testAggregateLog(): void
    {
        $log = $this->manager->getDriver('aggregate');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('prod', $log->getMonolog()->getName());
        self::assertCount(2, $log->getMonolog()->getHandlers());
    }

    public function testCreateAEmergencyLoggerIfNoLoggerIsFound(): void
    {
        $log = $this->manager->getDriver('notfound');

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('narrowspark', $log->getMonolog()->getName());
    }

    public function testCustomLoggerWithCallable(): void
    {
        $log = $this->manager->getDriver('custom_callable');
        $processor = $log->getMonolog()->getProcessors();

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('customCallable', $log->getMonolog()->getName());
        self::assertInstanceOf(DebugProcessor::class, $processor[0]);
    }

    public function testPushProcessorsToMonolog(): void
    {
        $this->manager->pushProcessor(new DebugProcessor());

        $log = $this->manager->getDriver('single');
        $processor = $log->getMonolog()->getProcessors();

        self::assertInstanceOf(Logger::class, $log);
        self::assertSame('prod', $log->getMonolog()->getName());
        self::assertInstanceOf(DebugProcessor::class, $processor[0]);
    }

    public function testGetChannelAliasForGetDriver(): void
    {
        self::assertEquals($this->manager->getDriver(), $this->manager->getChannel());
        self::assertEquals($this->manager->getDriver('single'), $this->manager->getChannel('single'));
    }

    public function testGetDriversLoggerHasEventManager(): void
    {
        $eventManagerMock = \Mockery::mock(EventManagerContract::class);
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

        $log = $this->manager->getDriver('newrelic');
        $handler = $log->getMonolog()->getHandlers()[0];

        self::assertSame('nr', $log->getMonolog()->getName());
        self::assertCount(1, $log->getMonolog()->getHandlers());
        self::assertInstanceOf(Logger::class, $log);
        self::assertInstanceOf(NewRelicHandler::class, $handler);
        self::assertInstanceOf(NormalizerFormatter::class, $handler->getFormatter());
    }

    public function testMonologHandlerWithNewRelicHandlerAndHtmlFormatter(): void
    {
        $this->manager->setContainer(new ArrayContainer([
            NewRelicHandler::class => new NewRelicHandler(),
            HtmlFormatter::class => new HtmlFormatter(),
        ]));

        $log = $this->manager->getDriver('newrelic_html');
        $handler = $log->getMonolog()->getHandlers()[0];

        self::assertSame('nr', $log->getMonolog()->getName());
        self::assertCount(1, $log->getMonolog()->getHandlers());
        self::assertInstanceOf(Logger::class, $log);
        self::assertInstanceOf(NewRelicHandler::class, $handler);
        self::assertInstanceOf(HtmlFormatter::class, $handler->getFormatter());
    }

    public function testExceptionOnInvalidVia(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Given custom logger [via_error] could not be resolved.');

        $this->manager->getDriver('via_error');
    }
}
