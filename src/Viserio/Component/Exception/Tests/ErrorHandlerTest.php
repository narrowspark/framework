<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use Exception;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Viserio\Component\Exception\ErrorHandler;

class ErrorHandlerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Viserio\Component\Exception\ErrorHandler
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->mock(LoggerInterface::class);

        $this->handler = new ErrorHandler([], $this->logger);
    }

    public function testReportError(): void
    {
        $exception = new Exception('Exception message');

        $this->logger->shouldReceive('error')
            ->once()
            ->withArgs(['Uncaught Exception: Exception message', Mockery::hasKey('exception')]);
        $this->logger->shouldReceive('critical')
            ->never();

        $this->handler->report($exception);
    }

    public function testReportCritical(): void
    {
        $exception = new FatalThrowableError(new Exception());

        $this->logger->shouldReceive('error')
            ->never();
        $this->logger->shouldReceive('critical')
            ->once();

        $this->handler->report($exception);
    }

    public function testShouldntReport(): void
    {
        $exception = new FatalThrowableError(new Exception());

        $this->logger->shouldReceive('critical')
            ->never();

        $this->handler->addShouldntReport($exception);
        $this->handler->report($exception);
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
