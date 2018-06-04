<?php
declare(strict_types=1);
namespace Viserio\Bridge\Monolog\Tests\Processor;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Monolog\Processor\DebugProcessor;

/**
 * @internal
 */
final class DebugProcessorTest extends TestCase
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $handler   = new TestHandler();
        $processor = new DebugProcessor();

        $this->logger = new Logger(__METHOD__, [$handler], [$processor]);
    }

    public function testGetLogsWithDebugProcessor(): void
    {
        $this->assertTrue($this->logger->error('error message'));
        $this->assertCount(1, $this->getDebugLogger()->getLogs());
    }

    public function testCountErrorsWithDebugProcessor(): void
    {
        $this->assertTrue($this->logger->debug('test message'));
        $this->assertTrue($this->logger->info('test message'));
        $this->assertTrue($this->logger->notice('test message'));
        $this->assertTrue($this->logger->warning('test message'));
        $this->assertTrue($this->logger->error('test message'));
        $this->assertTrue($this->logger->critical('test message'));
        $this->assertTrue($this->logger->alert('test message'));
        $this->assertTrue($this->logger->emergency('test message'));

        $this->assertSame(4, $this->getDebugLogger()->countErrors());
    }

    public function testGetLogsWithDebugProcessor2(): void
    {
        $handler = new TestHandler();
        $logger  = new Logger('test', [$handler]);
        $logger->pushProcessor(new DebugProcessor());
        $logger->addInfo('test');

        $this->assertCount(1, $this->getDebugLogger($logger)->getLogs());

        [$record] = $this->getDebugLogger($logger)->getLogs();

        $this->assertEquals('test', $record['message']);
        $this->assertEquals(Logger::INFO, $record['priority']);
    }

    public function testFlush(): void
    {
        $handler = new TestHandler();
        $logger  = new Logger('test', [$handler]);
        $logger->pushProcessor(new DebugProcessor());
        $logger->addInfo('test');

        $this->getDebugLogger($logger)->flush();

        $this->assertEmpty($this->getDebugLogger($logger)->getLogs());
        $this->assertSame(0, $this->getDebugLogger($logger)->countErrors());
    }

    /**
     * Returns a DebugProcessor instance if one is registered with this logger.
     *
     * @param null|\Monolog\Logger $logger
     *
     * @return null|\Viserio\Bridge\Monolog\Processor\DebugProcessor
     */
    private function getDebugLogger(Logger $logger = null): ?DebugProcessor
    {
        if ($logger === null) {
            $logger = $this->logger;
        }

        foreach ($logger->getProcessors() as $processor) {
            if ($processor instanceof DebugProcessor) {
                return $processor;
            }
        }

        return null;
    }
}
