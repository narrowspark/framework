<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests;

use ErrorException;
use Exception;
use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Exception\Displayers\HtmlDisplayer;
use Viserio\Exception\Displayers\JsonDisplayer;
use Viserio\Exception\Displayers\WhoopsDisplayer;
use Viserio\Exception\ExceptionIdentifier;
use Viserio\Exception\ExceptionInfo;
use Viserio\Exception\Filters\VerboseFilter;
use Viserio\Exception\Handler;
use Viserio\Exception\Transformers\CommandLineTransformer;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\StreamFactory;

class HandlerTest extends TestCase
{
    use MockeryTrait;

    public function getContainer()
    {
        $container = $this->mock(ContainerInterface::class);
        $log       = $this->mock(LoggerInterface::class);
        $log->shouldReceive('error');
        $container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(ExceptionIdentifier::class)
            ->andReturn(new ExceptionIdentifier());
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

        $handler->addDisplayer(new HtmlDisplayer($info, new ResponseFactory(), new StreamFactory(), ''));
        $handler->addDisplayer(new JsonDisplayer($info));
        $handler->addDisplayer(new JsonDisplayer($info));
        $handler->addDisplayer(new WhoopsDisplayer($info));

        self::assertSame(3, count($handler->getDisplayers()));
    }

    public function testAddAndGetTransformer()
    {
        $handler = new Handler($this->getContainer());

        $handler->addTransformer(new CommandLineTransformer());
        $handler->addTransformer(new CommandLineTransformer());

        self::assertSame(2, count($handler->getTransformers()));
    }

    public function testAddAndGetFilter()
    {
        $handler = new Handler($this->getContainer());

        $handler->addFilter(new VerboseFilter(true));
        $handler->addFilter(new VerboseFilter(true));

        self::assertSame(3, count($handler->getFilters()));
    }

    public function testReportError($value = '')
    {
        $exception = new Exception();
        $id        = (new ExceptionIdentifier())->identify($exception);

        $log = $this->mock(LoggerInterface::class);
        $log
            ->shouldReceive('error')
            ->once($exception, ['identification' => ['id' => $id]]);

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->twice()
            ->andReturn([]);
        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(RepositoryContract::class)
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
        $id        = (new ExceptionIdentifier())->identify($exception);

        $log = $this->mock(LoggerInterface::class);
        $log->shouldReceive('critical')
            ->once($exception, ['identification' => ['id' => $id]]);

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->twice()
            ->andReturn([]);

        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(RepositoryContract::class)
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
        $id        = (new ExceptionIdentifier())->identify($exception);

        $log = $this->mock(LoggerInterface::class);
        $log->shouldReceive('critical')
            ->never();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->andReturn([]);

        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(RepositoryContract::class)
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
            self::assertInstanceOf(ErrorException::class, $e);
        }
    }
}
