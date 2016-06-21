<?php
namespace Viserio\Log\Tests;

use Interop\Container\ContainerInterface as ContainerContract;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Mockery as Mock;
use Monolog\Logger;
use Monolog\Handler\{
    StreamHandler,
    RotatingFileHandler
};
use Viserio\Events\Dispatcher;
use Viserio\Log\Writer;
use Viserio\Log\Tests\Fixture\{
    ArrayableClass,
    JsonableClass
};

class WriterTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testSetAndGetDispatcher()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Writer($monolog);
        $writer->setEventDispatcher($this->getEventsDispatcher());

        $this->assertInstanceOf(Dispatcher::class, $writer->getEventDispatcher());
    }

    public function testGetMonolog()
    {
        $writer = new Writer(new Logger('name'));

        $this->assertInstanceOf(Logger::class, $writer->getMonolog());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
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

        $writer = new Writer($monolog, $this->getEventsDispatcher());
        $writer->log('debug', 'foo');
    }

    public function testWriterFiresEventsDispatcher()
    {
        $events = $this->getEventsDispatcher();
        $events->on(
            'viserio.log',
            function ($level, $message, array $context = array()) {
                $_SERVER['__log.level']   = $level;
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

        $writer = new Writer($monolog, $events);
        $writer->error('foo');

        $this->assertTrue(isset($_SERVER['__log.level']));
        $this->assertEquals('error', $_SERVER['__log.level']);

        unset($_SERVER['__log.level']);

        $this->assertTrue(isset($_SERVER['__log.message']));
        $this->assertEquals('foo', $_SERVER['__log.message']);

        unset($_SERVER['__log.message']);

        $this->assertTrue(isset($_SERVER['__log.context']));
        $this->assertEquals([], $_SERVER['__log.context']);

        unset($_SERVER['__log.context']);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetEventDispatcher()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();
        $writer = new Writer($monolog);
        $writer->getEventDispatcher();
    }

    public function testMessageInput()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();
        $monolog
            ->shouldReceive('info')
            ->twice();
        $monolog
            ->shouldReceive('debug')
            ->once();

        $writer = new Writer($monolog);
        $writer->log('info', ['message' => true]);
        $writer->log('debug', new ArrayableClass());
        $writer->log('info', new JsonableClass());
    }

    protected function getEventsDispatcher()
    {
        return new Dispatcher(
            $this->mock(ContainerContract::class)
        );
    }
}
