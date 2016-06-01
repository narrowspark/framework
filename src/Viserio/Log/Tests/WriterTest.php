<?php
namespace Viserio\Log\Test;

use Mockery as Mock;
use Viserio\Events\Dispatcher;
use Viserio\Log\Writer;

class WriterTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testFileHandlerCanBeAdded()
    {
        $writer = new Writer($monolog = Mock::mock('Monolog\Logger'), $this->getEventsDispatcher());
        $monolog->shouldReceive('pushHandler')->once()->with(Mock::type('Monolog\Handler\StreamHandler'));
        $writer->useFiles(__DIR__);
    }

    public function testRotatingFileHandlerCanBeAdded()
    {
        $writer = new Writer($monolog = Mock::mock('Monolog\Logger'), $this->getEventsDispatcher());
        $monolog->shouldReceive('pushHandler')->once()->with(Mock::type('Monolog\Handler\RotatingFileHandler'));
        $writer->useDailyFiles(__DIR__, 5);
    }

    public function testErrorLogHandlerCanBeAdded()
    {
        $writer = new Writer($monolog = Mock::mock('Monolog\Logger'), $this->getEventsDispatcher());
        $monolog->shouldReceive('pushHandler')->once()->with(Mock::type('Monolog\Handler\ErrorLogHandler'));
        $writer->useErrorLog();
    }

    public function testMethodsPassErrorAdditionsToMonolog()
    {
        $writer = new Writer($monolog = Mock::mock('Monolog\Logger'), $this->getEventsDispatcher());
        $monolog->shouldReceive('error')->once()->with('foo', []);
        $writer->error('foo');
    }

    public function testWriterFiresEventsDispatcher()
    {
        $events = $this->getEventsDispatcher();
        $writer = new Writer($monolog = Mock::mock('Monolog\Logger'), $events);
        $monolog->shouldReceive('error')->once()->with('foo', []);
        // $events->on(
        //     'Viserio.log',
        //     function ($level, $message, array $context = array()) {
        //         $_SERVER['__log.level']   = $level;
        //         $_SERVER['__log.message'] = $message;
        //         $_SERVER['__log.context'] = $context;
        //     }
        // );
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
        $writer = new Writer($monolog = Mock::mock('Monolog\Logger'), $this->getEventsDispatcher());
        // $writer->on(function () {

        // });
    }

    public function testListenShortcut()
    {
        $writer = new Writer($monolog = Mock::mock('Monolog\Logger'), $events = Mock::mock('Viserio\Contracts\Events\Dispatcher'));

        $callback = function () {
            return 'success';
        };

        //$events->shouldReceive('listen')->with('Viserio.log', $callback)->once();
        //$writer->on($callback);
    }

    protected function getEventsDispatcher()
    {
        return new Dispatcher(
            Mock::mock('Symfony\Component\EventDispatcher\EventDispatcher'),
            Mock::mock('Viserio\Container\Container')
        );
    }
}
