<?php
namespace Viserio\Log\Test;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Monolog\Logger;
use Monolog\Handler\HandlerInterface;
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

    public function testParseHandler()
    {
        $logger = $this->mock(Logger::class);
        $logger->shouldReceive('pushHandler')
            ->once()
            ->andReturn(HandlerInterface::class);

        $handler = new HandlerParser($logger);
        $handler->parseHandler('chromePHP', '', 'info');
    }
}
