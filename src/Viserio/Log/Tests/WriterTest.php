<?php
declare(strict_types=1);
namespace Viserio\Log\Tests;

use Interop\Container\ContainerInterface as ContainerContract;
use Mockery as Mock;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Events\Dispatcher;
use Viserio\Log\Tests\Fixture\ArrayableClass;
use Viserio\Log\Tests\Fixture\JsonableClass;
use Viserio\Log\Writer;

class WriterTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetMonolog()
    {
        $writer = new Writer(new Logger('name'));

        self::assertInstanceOf(Logger::class, $writer->getMonolog());
    }

    public function testCallToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();
        $monolog
            ->shouldReceive('getName')
            ->once();

        $writer = new Writer($monolog);
        $writer->getName();
    }

    public function testFileHandlerCanBeAdded()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushHandler')
            ->once()
            ->with(Mock::type(StreamHandler::class));
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->useFiles(__DIR__);
    }

    public function testRotatingFileHandlerCanBeAdded()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushHandler')
            ->once()
            ->with(Mock::type(RotatingFileHandler::class));
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->useDailyFiles(__DIR__, 5);
    }

    public function testMethodsPassErrorAdditionsToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('error')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->error('foo');
    }

    public function testMethodsPassEmergencyAdditionsToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('emergency')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->emergency('foo');
    }

    public function testMethodsPassAlertAdditionsToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('alert')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->alert('foo');
    }

    public function testMethodsPassCriticalAdditionsToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('critical')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->critical('foo');
    }

    public function testMethodsPassWarningAdditionsToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('warning')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->warning('foo');
    }

    public function testMethodsPassNoticeAdditionsToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('notice')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->notice('foo');
    }

    public function testMethodsPassInfoAdditionsToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('info')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->info('foo');
    }

    public function testMethodsPassDebugAdditionsToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('debug')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->debug('foo');
    }

    public function testMethodsPassDebugWithLogAdditionsToMonolog()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('debug')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($this->getEventsDispatcher());
        $writer->log('debug', 'foo');
    }

    public function testWriterFiresEventsDispatcher()
    {
        $events = $this->getEventsDispatcher();
        $events->attach(
            'viserio.log',
            function ($level, $message, array $context = []) {
                $_SERVER['__log.level'] = $level;
                $_SERVER['__log.message'] = $message;
                $_SERVER['__log.context'] = $context;
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

        $writer = new Writer($monolog);
        $writer->setEventsDispatcher($events);
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

    public function testMessageInput()
    {
        $monolog = $this->mock(Logger::class);
        $monolog->shouldReceive('pushProcessor')
            ->once();
        $monolog->shouldReceive('info')
            ->once();
        $monolog->shouldReceive('warning')
            ->once()
            ->with('{"message": true}', []);
        $monolog->shouldReceive('debug')
            ->once()
            ->with(var_export((new ArrayableClass())->toArray(), true), []);

        $writer = new Writer($monolog);
        $writer->log('info', ['message' => true]);
        $writer->log('debug', new ArrayableClass());
        $writer->log('warning', new JsonableClass());
    }

    protected function getEventsDispatcher()
    {
        return new Dispatcher(
            $this->mock(ContainerContract::class)
        );
    }
}
