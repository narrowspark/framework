<?php
namespace Viserio\Log\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\GitProcessor;
use Viserio\Log\HandlerParser;

class HandlerParserTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testGetMonolog()
    {
        $handler = new HandlerParser($this->mock(Logger::class));

        $this->assertInstanceOf(Logger::class, $handler->getMonolog());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testParseHandlerToThrowExceptionForLog()
    {
        $handler = new HandlerParser($this->mock(Logger::class));

        $this->assertInstanceOf(
            HandlerInterface::class,
            $handler->parseHandler('chromePHP', '')
        );
    }

    /**
     * @expectedException RuntimeException
     */
    public function testParseHandlerToThrowExceptionForHandler()
    {
        $handler = new HandlerParser($this->mock(Logger::class));

        $this->assertInstanceOf(
            HandlerInterface::class,
            $handler->parseHandler('dontexist', '', 'debug')
        );
    }

    public function testParseHandler()
    {
        $logger = $this->mock(Logger::class);
        $logger->shouldReceive('pushHandler')
            ->once()
            ->andReturn(HandlerInterface::class);

        $handler = new HandlerParser($logger);
        $handler->parseHandler('chromePHP', '', 'info');
    }

    public function testParseHandlerWithProcessors()
    {
        $logger = $this->mock(Logger::class);
        $logger->shouldReceive('pushHandler')
            ->once()
            ->andReturn(HandlerInterface::class);
        $handler = $this->mock(HandlerInterface::class);
        $handler
            ->shouldReceive('pushProcessor')
            ->twice();

        $parser = new HandlerParser($logger);
        $parser->parseHandler(
            $handler,
            '',
            'info',
            [
                PsrLogMessageProcessor::class => '',
                GitProcessor::class => ''
            ]
        );
    }

    public function testParseHandlerWithProcessor()
    {
        $logger = $this->mock(Logger::class);
        $logger->shouldReceive('pushHandler')
            ->once()
            ->andReturn(HandlerInterface::class);
        $handler = $this->mock(HandlerInterface::class);
        $handler
            ->shouldReceive('pushProcessor')
            ->once();

        $parser = new HandlerParser($logger);
        $parser->parseHandler(
            $handler,
            '',
            'info',
            new PsrLogMessageProcessor()
        );
    }
}
