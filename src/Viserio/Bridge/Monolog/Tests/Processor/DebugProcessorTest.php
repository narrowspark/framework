<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Bridge\Monolog\Tests\Processor;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Monolog\Processor\DebugProcessor;
use Viserio\Contract\Log\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class DebugProcessorTest extends TestCase
{
    /** @var \Monolog\Logger */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $handler = new TestHandler();
        $processor = new DebugProcessor();

        $this->logger = new Logger(__METHOD__, [$handler], [$processor]);
    }

    public function testGetLogsWithDebugProcessor(): void
    {
        $this->logger->error('error message');

        self::assertCount(1, $this->getDebugLogger()->getLogs());
    }

    public function testCountErrorsWithDebugProcessor(): void
    {
        $this->logger->debug('test message');
        $this->logger->info('test message');
        $this->logger->notice('test message');
        $this->logger->warning('test message');
        $this->logger->error('test message');
        $this->logger->critical('test message');
        $this->logger->alert('test message');
        $this->logger->emergency('test message');

        self::assertSame(4, $this->getDebugLogger()->countErrors());
    }

    public function testGetLogsWithDebugProcessor2(): void
    {
        $handler = new TestHandler();
        $logger = new Logger('test', [$handler]);
        $logger->pushProcessor(new DebugProcessor());
        $logger->info('test');

        self::assertCount(1, $this->getDebugLogger($logger)->getLogs());

        [$record] = $this->getDebugLogger($logger)->getLogs();

        self::assertEquals('test', $record['message']);
        self::assertEquals(Logger::INFO, $record['priority']);
    }

    public function testFlush(): void
    {
        $handler = new TestHandler();
        $logger = new Logger('test', [$handler]);
        $logger->pushProcessor(new DebugProcessor());
        $logger->info('test');

        $this->getDebugLogger($logger)->reset();

        self::assertEmpty($this->getDebugLogger($logger)->getLogs());
        self::assertSame(0, $this->getDebugLogger($logger)->countErrors());
    }

    /**
     * Returns a DebugProcessor instance if one is registered with this logger.
     */
    private function getDebugLogger(?Logger $logger = null): DebugProcessor
    {
        if ($logger === null) {
            $logger = $this->logger;
        }

        foreach ($logger->getProcessors() as $processor) {
            if ($processor instanceof DebugProcessor) {
                return $processor;
            }
        }

        throw new RuntimeException('This will never happen.');
    }
}
