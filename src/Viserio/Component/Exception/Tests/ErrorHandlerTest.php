<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use Error;
use Exception;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Viserio\Component\Console\Output\SpyOutput;
use Viserio\Component\Exception\Console\SymfonyConsoleOutput;
use Viserio\Component\Exception\ErrorHandler;

class ErrorHandlerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Psr\Log\LoggerInterface
     */
    private $loggger;

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

    public function testHandleExceptionOnCli(): void
    {
        $error  = new Error();
        $output = new SpyOutput();

        $this->handler->setConsoleOutput(new SymfonyConsoleOutput($output));
        $this->handler->handleException($error);

        $file = __FILE__;

        self::assertSame(
            "
Symfony\Component\Debug\Exception\FatalErrorException : 

at $file : 77
73:     }
74: 
75:     public function testHandleExceptionOnCli()
76:     {
77:         \$error  = new Error();
78:         \$output = new SpyOutput();
79: 
80:         \$this->handler->setConsoleOutput(new SymfonyConsoleOutput(\$output));
81:         \$this->handler->handleException(\$error);
82: 

Exception trace:

1   Symfony\Component\Debug\Exception\FatalErrorException::__construct(\"\")
    $file : 77

    /var/www/framework/vendor/phpunit/phpunit/phpunit : 0


",
            $output->output
        );
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
