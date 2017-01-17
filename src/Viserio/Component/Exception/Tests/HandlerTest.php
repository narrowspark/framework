<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use ErrorException;
use Exception;
use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Exception\Displayers\HtmlDisplayer;
use Viserio\Component\Exception\Displayers\JsonDisplayer;
use Viserio\Component\Exception\Displayers\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionIdentifier;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filters\VerboseFilter;
use Viserio\Component\Exception\Handler;
use Viserio\Component\Exception\Transformers\CommandLineTransformer;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;

class HandlerTest extends TestCase
{
    use MockeryTrait;

    public function testAddAndGetDisplayer()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($this->mock(LoggerInterface::class));
        $handler = new Handler($container);

        $info = $this->mock(ExceptionInfo::class);

        $handler->addDisplayer(new HtmlDisplayer($info, new ResponseFactory(), new StreamFactory(), ''));
        $handler->addDisplayer(new JsonDisplayer($info, new ResponseFactory(), new StreamFactory()));
        $handler->addDisplayer(new JsonDisplayer($info, new ResponseFactory(), new StreamFactory()));
        $handler->addDisplayer(new WhoopsDisplayer($info));

        self::assertSame(3, count($handler->getDisplayers()));
    }

    public function testAddAndGetTransformer()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($this->mock(LoggerInterface::class));
        $handler = new Handler($container);

        $handler->addTransformer(new CommandLineTransformer());
        $handler->addTransformer(new CommandLineTransformer());

        self::assertSame(1, count($handler->getTransformers()));
    }

    public function testAddAndGetFilter()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($this->mock(LoggerInterface::class));
        $handler = new Handler($container);

        $handler->addFilter(new VerboseFilter(true));
        $handler->addFilter(new VerboseFilter(true));

        self::assertSame(1, count($handler->getFilters()));
    }

    public function testReportError()
    {
        $exception = new Exception();

        $log = $this->mock(LoggerInterface::class);
        $log->shouldReceive('error')
            ->once();
        $log->shouldReceive('critical')
            ->never();
        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);

        $handler = new Handler($container);

        $handler->report($exception);
    }

    public function testReportCritical()
    {
        $exception = new FatalThrowableError(new Exception());

        $log = $this->mock(LoggerInterface::class);
        $log->shouldReceive('error')
            ->never();
        $log->shouldReceive('critical')
            ->once();

        $container = $this->getContainer();
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

        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);
        $handler = new Handler($container);

        $handler->addShouldntReport($exception);

        $handler->report($exception);
    }

    public function testHandleError()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($this->mock(LoggerInterface::class));
        $handler = new Handler($container);

        try {
            $handler->handleError(E_PARSE, 'test', '', 0, null);
        } catch (ErrorException $e) {
            self::assertInstanceOf(ErrorException::class, $e);
        }
    }

    private function getContainer()
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(RepositoryContract::class)
            ->andReturn(['viserio' => ['exception' => ['env' => 'dev', 'default_displayer' => HtmlDisplayer::class]]]);
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
}
