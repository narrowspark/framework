<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests;

use ErrorException;
use Exception;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Log\LoggerInterface;
use Viserio\Contracts\Config\Manager as ConfigManagerContract;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Viserio\Exception\Displayers\HtmlDisplayer;
use Viserio\Exception\Displayers\JsonDisplayer;
use Viserio\Exception\Displayers\WhoopsDisplayer;
use Viserio\Exception\ExceptionIdentifier;
use Viserio\Exception\ExceptionInfo;
use Viserio\Exception\Filters\VerboseFilter;
use Viserio\Exception\Handler;
use Viserio\Exception\Transformers\CommandLineTransformer;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function getContainer()
    {
        $container = $this->mock(ContainerInterface::class);
        $log = $this->mock(LoggerInterface::class);
        $log->shouldReceive('error');
        $container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(ExceptionIdentifier::class)
            ->andReturn( new ExceptionIdentifier());
        $container->shouldReceive('get')
            ->with(ResponseInterface::class)
            ->andReturn($this->mock(ResponseInterface::class));
        $container->shouldReceive('get')
            ->with(ServerRequestInterface::class)
            ->andReturn($this->mock(ServerRequestInterface::class));

        return $container;
    }

    public function testAddAndGetDisplayer()
    {
        $handler = new Handler($this->getContainer());

        $info = $this->mock(ExceptionInfo::class);

        $handler->addDisplayer(new HtmlDisplayer($info, ''));
        $handler->addDisplayer(new JsonDisplayer($info));
        $handler->addDisplayer(new JsonDisplayer($info));
        $handler->addDisplayer(new WhoopsDisplayer($info));

        $this->assertSame(3, count($handler->getDisplayers()));
    }

    public function testAddAndGetTransformer()
    {
        $handler = new Handler($this->getContainer());

        $handler->addTransformer(new CommandLineTransformer());
        $handler->addTransformer(new CommandLineTransformer());

        $this->assertSame(2, count($handler->getTransformers()));
    }

    public function testAddAndGetFilter()
    {
        $handler = new Handler($this->getContainer());

        $handler->addFilter(new VerboseFilter(true));
        $handler->addFilter(new VerboseFilter(true));

        $this->assertSame(3, count($handler->getFilters()));
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
        $config->shouldReceive('get')
            ->twice()
            ->andReturn([]);
        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(ConfigManagerContract::class)
            ->andReturn($config);
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);

        $handler = new Handler($container);

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

        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(ConfigManagerContract::class)
            ->andReturn($config);
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);
        $handler = new Handler($container);

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

        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(ConfigManagerContract::class)
            ->andReturn($config);
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);
        $handler = new Handler($container);

        $handler->addShouldntReport($exception);

        $handler->report($exception);
    }

    public function testHandleError()
    {
        $handler = new Handler($this->getContainer());

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

        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(ConfigManagerContract::class)
            ->andReturn($config);
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);
        $container->shouldReceive('get')
            ->with(CommandLineTransformer::class)
            ->andReturn(new CommandLineTransformer());

        $handler = new Handler($container);

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

        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(ConfigManagerContract::class)
            ->andReturn($config);
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);
        $container->shouldReceive('get')
            ->with(CommandLineTransformer::class)
            ->andReturn(new CommandLineTransformer());

        $handler = new Handler($container);


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
