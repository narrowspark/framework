<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests;

use Mockery as Mock;
use Monolog\Formatter\ChromePHPFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\PsrLogMessageProcessor;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Log\HandlerParser;

class HandlerParserTest extends TestCase
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
        $handler = new HandlerParser($this->mock(Logger::class));

        self::assertInstanceOf(Logger::class, $handler->getMonolog());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseHandlerToThrowExceptionForLog()
    {
        $handler = new HandlerParser($this->mock(Logger::class));

        self::assertInstanceOf(
            HandlerInterface::class,
            $handler->parseHandler('chromePHP', '')
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testParseHandlerToThrowExceptionForHandler()
    {
        $handler = new HandlerParser($this->mock(Logger::class));

        $handler->parseHandler('dontexist', '', 'debug');
    }

    /**
     * @expectedException \RuntimeException
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
                GitProcessor::class           => '',
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
     * @expectedException \InvalidArgumentException
     */
    public function testParseHandlerWithFormatterTothrowException()
    {
        $logger  = $this->mock(Logger::class);
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
     *
     * @param mixed $formatter
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
