<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge\Monolog;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\DataCollector\Bridge\Monolog\DebugProcessor;

class DebugProcessorTest extends TestCase
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public function setUp(): void
    {
        parent::setUp();

        $handler   = new TestHandler();
        $processor = new DebugProcessor();

        $this->logger = new Logger(__METHOD__, [$handler], [$processor]);
    }

    public function testGetLogsWithDebugProcessor(): void
    {
        self::assertTrue($this->logger->error('error message'));
        self::assertCount(1, $this->getDebugLogger()->getLogs());
    }

    public function testCountErrorsWithDebugProcessor(): void
    {
        self::assertTrue($this->logger->debug('test message'));
        self::assertTrue($this->logger->info('test message'));
        self::assertTrue($this->logger->notice('test message'));
        self::assertTrue($this->logger->warning('test message'));
        self::assertTrue($this->logger->error('test message'));
        self::assertTrue($this->logger->critical('test message'));
        self::assertTrue($this->logger->alert('test message'));
        self::assertTrue($this->logger->emergency('test message'));

        self::assertSame(4, $this->getDebugLogger()->countErrors());
    }

    public function testGetLogsWithDebugProcessor2(): void
    {
        $handler = new TestHandler();
        $logger  = new Logger('test', [$handler]);
        $logger->pushProcessor(new DebugProcessor());
        $logger->addInfo('test');

        self::assertCount(1, $this->getDebugLogger($logger)->getLogs());

        [$record] = $this->getDebugLogger($logger)->getLogs();

        self::assertEquals('test', $record['message']);
        self::assertEquals(Logger::INFO, $record['priority']);
    }

    public function testFlush(): void
    {
        $handler = new TestHandler();
        $logger  = new Logger('test', [$handler]);
        $logger->pushProcessor(new DebugProcessor());
        $logger->addInfo('test');

        $this->getDebugLogger($logger)->flush();

        self::assertEmpty($this->getDebugLogger($logger)->getLogs());
        self::assertSame(0, $this->getDebugLogger($logger)->countErrors());
    }

    /**
     * Returns a DebugProcessor instance if one is registered with this logger.
     *
     * @param null|\Monolog\Logger $logger
     *
     * @return null|\Viserio\Component\Profiler\DataCollector\Bridge\Monolog\DebugProcessor
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
