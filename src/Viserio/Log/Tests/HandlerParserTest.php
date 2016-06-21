<?php
namespace Viserio\Log\Tests;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
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

        $handler->parseHandler('dontexist', '', 'debug');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testParseHandlerToThrowExceptionForHandlerWithObject()
    {
        $handler = new HandlerParser($this->mock(Logger::class));

        $handler->parseHandler($handler, '', 'debug');
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

    public function testParseHandlerWithObjectFormatter()
    {
        $logger = $this->mock(Logger::class);
        $logger->shouldReceive('pushHandler')
            ->once()
            ->andReturn(HandlerInterface::class);
        $handler = $this->mock(HandlerInterface::class);
        $handler
            ->shouldReceive('setFormatter')
            ->once();

        $parser = new HandlerParser($logger);
        $parser->parseHandler(
            $handler,
            '',
            'info',
            null,
            new ChromePHPFormatter()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testParseHandlerWithFormatterTothrowException()
    {
        $logger = $this->mock(Logger::class);
        $handler = $this->mock(HandlerInterface::class);

        $parser = new HandlerParser($logger);
        $parser->parseHandler(
            $handler,
            '',
            'info',
            null,
            'dontexist'
        );
    }

    /**
     * @dataProvider formatterProvider
     */
    public function testParseHandlerWithFormatterWithDataProvider($formatter)
    {
        $logger = $this->mock(Logger::class);
        $logger->shouldReceive('pushHandler')
            ->once()
            ->andReturn(HandlerInterface::class);
        $handler = $this->mock(HandlerInterface::class);
        $handler
            ->shouldReceive('setFormatter')
            ->once();

        $parser = new HandlerParser($logger);
        $parser->parseHandler(
            $handler,
            '',
            'info',
            null,
            $formatter
        );
    }

    public function formatterProvider()
    {
        return [
            ['line'],
            ['html'],
            ['normalizer'],
            ['scalar'],
            ['json'],
            ['wildfire'],
            ['chrome'],
            ['gelf'],
        ];
    }
}
