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
        static::assertTrue($this->logger->error('error message'));
        static::assertCount(1, $this->getDebugLogger()->getLogs());
    }

    public function testCountErrorsWithDebugProcessor(): void
    {
        static::assertTrue($this->logger->debug('test message'));
        static::assertTrue($this->logger->info('test message'));
        static::assertTrue($this->logger->notice('test message'));
        static::assertTrue($this->logger->warning('test message'));
        static::assertTrue($this->logger->error('test message'));
        static::assertTrue($this->logger->critical('test message'));
        static::assertTrue($this->logger->alert('test message'));
        static::assertTrue($this->logger->emergency('test message'));

        static::assertSame(4, $this->getDebugLogger()->countErrors());
    }

    public function testGetLogsWithDebugProcessor2(): void
    {
        $handler = new TestHandler();
        $logger  = new Logger('test', [$handler]);
        $logger->pushProcessor(new DebugProcessor());
        $logger->addInfo('test');

        static::assertCount(1, $this->getDebugLogger($logger)->getLogs());

        [$record] = $this->getDebugLogger($logger)->getLogs();

        static::assertEquals('test', $record['message']);
        static::assertEquals(Logger::INFO, $record['priority']);
    }

    public function testFlush(): void
    {
        $handler = new TestHandler();
        $logger  = new Logger('test', [$handler]);
        $logger->pushProcessor(new DebugProcessor());
        $logger->addInfo('test');

        $this->getDebugLogger($logger)->flush();

        static::assertEmpty($this->getDebugLogger($logger)->getLogs());
        static::assertSame(0, $this->getDebugLogger($logger)->countErrors());
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
