<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests;

use ErrorException;
use Exception;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Log\LoggerInterface;
use Viserio\Contracts\Config\Manager as ConfigManagerContract;
use Viserio\Contracts\Exception\Exception\FatalThrowableError;
use Viserio\Exception\Displayers\HtmlDisplayer;
use Viserio\Exception\Displayers\JsonDisplayer;
use Viserio\Exception\ExceptionIdentifier;
use Viserio\Exception\ExceptionInfo;
use Viserio\Exception\Filters\VerboseFilter;
use Viserio\Exception\Handler;
use Viserio\Exception\Transformers\CommandLineTransformer;

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testAddAndGetDisplayer()
    {
        $handler = new Handler(
            $this->mock(ConfigManagerContract::class),
            $this->mock(LoggerInterface::class),
            new ExceptionIdentifier()
        );

        $info = $this->mock(ExceptionInfo::class);

        $handler->addDisplayer(new HtmlDisplayer($info, ''));
        $handler->addDisplayer(new JsonDisplayer($info));
        $handler->addDisplayer(new JsonDisplayer($info));

        $this->assertSame(2, count($handler->getDisplayers()));
    }

    public function testAddAndGetTransformer()
    {
        $handler = new Handler(
            $this->mock(ConfigManagerContract::class),
            $this->mock(LoggerInterface::class),
            new ExceptionIdentifier()
        );

        $handler->addTransformer(new CommandLineTransformer());
        $handler->addTransformer(new CommandLineTransformer());

        $this->assertSame(1, count($handler->getTransformers()));
    }

    public function testAddAndGetFilter()
    {
        $handler = new Handler(
            $this->mock(ConfigManagerContract::class),
            $this->mock(LoggerInterface::class),
            new ExceptionIdentifier()
        );

        $handler->addFilter($this->mock(VerboseFilter::class));
        $handler->addFilter($this->mock(VerboseFilter::class));

        $this->assertSame(1, count($handler->getFilters()));
    }

    public function testReportError($value = '')
    {
        $exception = new Exception();
        $id = (new ExceptionIdentifier())->identify($exception);

        $log = $this->mock(LoggerInterface::class);
        $log
            ->shouldReceive('error')
            ->once($exception, ['identification' => ['id' => $id]]);

        $config = $this->mock(ConfigManagerContract::class);
        $config
            ->shouldReceive('get')
            ->twice()
            ->andReturn([]);

        $handler = new Handler(
            $config,
            $log,
            new ExceptionIdentifier()
        );

        $handler->report($exception);
    }

    public function testReportCritical($value = '')
    {
        $exception = new FatalThrowableError(new Exception());
        $id = (new ExceptionIdentifier())->identify($exception);

        $log = $this->mock(LoggerInterface::class);
        $log
            ->shouldReceive('critical')
            ->once($exception, ['identification' => ['id' => $id]]);

        $config = $this->mock(ConfigManagerContract::class);
        $config
            ->shouldReceive('get')
            ->twice()
            ->andReturn([]);

        $handler = new Handler(
            $config,
            $log,
            new ExceptionIdentifier()
        );

        $handler->report($exception);
    }

    public function testShouldntReport()
    {
        $exception = new FatalThrowableError(new Exception());
        $id = (new ExceptionIdentifier())->identify($exception);

        $log = $this->mock(LoggerInterface::class);
        $log
            ->shouldReceive('critical')
            ->never();

        $config = $this->mock(ConfigManagerContract::class);
        $config
            ->shouldReceive('get')
            ->once()
            ->andReturn([]);

        $handler = new Handler(
            $config,
            $log,
            new ExceptionIdentifier()
        );

        $handler->addShouldntReport($exception);

        $handler->report($exception);
    }

    public function testHandleError()
    {
        $handler = new Handler(
            $this->mock(ConfigManagerContract::class),
            $this->mock(LoggerInterface::class),
            new ExceptionIdentifier()
        );

        try {
            $handler->handleError(E_PARSE, 'test', '', 0, null);
        } catch (ErrorException $e) {
            $this->assertInstanceOf(ErrorException::class, $e);
        }
    }

    public function testHandleException()
    {
        $log = $this->mock(LoggerInterface::class);
        $log
            ->shouldReceive('critical')
            ->once();

        $config = $this->mock(ConfigManagerContract::class);
        $config
            ->shouldReceive('get')
            ->andReturn([]);

        $handler = new Handler(
            $config,
            $log,
            new ExceptionIdentifier()
        );

        ob_start();

        try {
            $handler->handleException(new Exception());
        } catch (FatalThrowableError $e) {
            $this->assertInstanceOf(FatalThrowableError::class, $e);
        }

        ob_end_clean();
    }

    public function testFormatedHandleException()
    {
        $log = $this->mock(LoggerInterface::class);
        $log
            ->shouldReceive('critical')
            ->once();

        $config = $this->mock(ConfigManagerContract::class);
        $config
            ->shouldReceive('get')
            ->andReturn([]);

        $handler = new Handler(
            $config,
            $log,
            new ExceptionIdentifier()
        );

        $handler->addTransformer(new CommandLineTransformer());

        ob_start();

        try {
            $handler->handleException(new Exception());
        } catch (FatalThrowableError $e) {
            $this->assertInstanceOf(FatalThrowableError::class, $e);
        }

        ob_end_clean();
    }
}
