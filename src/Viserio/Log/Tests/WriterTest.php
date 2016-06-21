<?php
namespace Viserio\Log\Test;

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

class WriterTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

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
    public function testListenShortcutFailsWithNoDispatcher()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();
        $writer = new Writer($monolog);
        $writer->on(function () {

        });
    }

    public function testListenShortcut()
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $callback = function () {
            $_SERVER['__log.message'] = 'success';
        };

        $writer = new Writer($monolog, $this->getEventsDispatcher());
        $writer->on($callback);
        $writer->getEventDispatcher()->emit('viserio.log');

        $this->assertTrue(isset($_SERVER['__log.message']));
        $this->assertEquals('success', $_SERVER['__log.message']);

        unset($_SERVER['__log.message']);
    }

    protected function getEventsDispatcher()
    {
        return new Dispatcher(
            $this->mock(ContainerContract::class)
        );
    }
}
