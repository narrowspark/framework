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
     * @var \Psr\Log\LoggerInterface|\Mockery\MockInterface
     */
    private $loggger;

    /**
     * @var \Viserio\Component\Exception\Handler
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->loggger = $this->mock(LoggerInterface::class);

        $this->handler = new ErrorHandler([], $this->loggger);
    }

    public function testReportError(): void
    {
        $exception = new Exception('Exception message');

        $this->loggger->shouldReceive('error')
            ->once()
            ->withArgs(['Uncaught Exception: Exception message', Mockery::hasKey('exception')]);
        $this->loggger->shouldReceive('critical')
            ->never();

        $this->handler->report($exception);
    }

    public function testReportCritical(): void
    {
        $exception = new FatalThrowableError(new Exception());

        $this->loggger->shouldReceive('error')
            ->never();
        $this->loggger->shouldReceive('critical')
            ->once();

        $this->handler->report($exception);
    }

    public function testShouldntReport(): void
    {
        $exception = new FatalThrowableError(new Exception());

        $this->loggger->shouldReceive('critical')
            ->never();

        $this->handler->addShouldntReport($exception);
        $this->handler->report($exception);
    }

    /**
     * {@inheritdoc}
     */
    protected function assertPreConditions(): void
    {
        parent::assertPreConditions();

        $this->allowMockingNonExistentMethods(true);
    }
}
