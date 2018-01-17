<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests;

use Mockery as Mock;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Log\Log as LogContract;
use Viserio\Component\Events\EventManager;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Tests\Fixture\ArrayableClass;
use Viserio\Component\Log\Tests\Fixture\JsonableClass;
use Viserio\Component\Log\Logger;

class WriterTest extends MockeryTestCase
{
    public function testGetMonolog(): void
    {
        $writer = new Logger(new HandlerParser(new Logger('name')));

        self::assertInstanceOf(Logger::class, $writer->getMonolog());
    }

    public function testCallToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();
        $monolog
            ->shouldReceive('getName')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->getName();
    }

    public function testFileHandlerCanBeAdded(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushHandler')
            ->once()
            ->with(Mock::type(StreamHandler::class));
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->useFiles(__DIR__);
    }

    public function testRotatingFileHandlerCanBeAdded(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushHandler')
            ->once()
            ->with(Mock::type(RotatingFileHandler::class));
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->useDailyFiles(__DIR__, 5);
    }

    public function testMethodsPassErrorAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('error')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->error('foo');
    }

    public function testMethodsPassEmergencyAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('emergency')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->emergency('foo');
    }

    public function testMethodsPassAlertAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('alert')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->alert('foo');
    }

    public function testMethodsPassCriticalAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('critical')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->critical('foo');
    }

    public function testMethodsPassWarningAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('warning')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->warning('foo');
    }

    public function testMethodsPassNoticeAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('notice')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->notice('foo');
    }

    public function testMethodsPassInfoAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('info')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->info('foo');
    }

    public function testMethodsPassDebugAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('debug')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->debug('foo');
    }

    public function testMethodsPassDebugWithLogAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('debug')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->log('debug', 'foo');
    }

    public function testWriterTriggerEventManager(): void
    {
        $events = new EventManager();
        $events->attach(
            LogContract::MESSAGE,
            function ($event): void {
                $_SERVER['__log.level'] = $event->getLevel();
                $_SERVER['__log.message'] = $event->getMessage();
                $_SERVER['__log.context'] = $event->getContext();
            }
        );
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('error')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager($events);
        $writer->error('foo');

        self::assertTrue(isset($_SERVER['__log.level']));
        self::assertEquals('error', $_SERVER['__log.level']);

        unset($_SERVER['__log.level']);

        self::assertTrue(isset($_SERVER['__log.message']));
        self::assertEquals('foo', $_SERVER['__log.message']);

        unset($_SERVER['__log.message']);

        self::assertTrue(isset($_SERVER['__log.context']));
        self::assertEquals([], $_SERVER['__log.context']);

        unset($_SERVER['__log.context']);
    }

    public function testMessageInput(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog->shouldReceive('pushProcessor')
            ->once();
        $monolog->shouldReceive('info')
            ->once();
        $monolog->shouldReceive('warning')
            ->once()
            ->with(\json_encode(['message' => true], JSON_PRETTY_PRINT), []);
        $monolog->shouldReceive('debug')
            ->once()
            ->with(\var_export((new ArrayableClass())->toArray(), true), []);

        $writer = new Logger(new HandlerParser($monolog));
        $writer->log('info', ['message' => true]);
        $writer->log('debug', new ArrayableClass());
        $writer->log('warning', new JsonableClass());
    }
}
