<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use ErrorException;
use Exception;
use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionIdentifier;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Handler;
use Viserio\Component\Exception\Transformer\CommandLineTransformer;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;

class HandlerTest extends MockeryTestCase
{
    public function testAddAndGetDisplayer()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($this->mock(LoggerInterface::class));
        $handler = new Handler($container);

        $info = $this->mock(ExceptionInfo::class);

        $handler->addDisplayer(new HtmlDisplayer($info, new ResponseFactory(), new StreamFactory(), $container));
        $handler->addDisplayer(new JsonDisplayer($info, new ResponseFactory(), new StreamFactory()));
        $handler->addDisplayer(new JsonDisplayer($info, new ResponseFactory(), new StreamFactory()));
        $handler->addDisplayer(new WhoopsDisplayer());

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

        $handler->addFilter(new VerboseFilter($container));
        $handler->addFilter(new VerboseFilter($container));

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
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->with('viserio')
            ->andReturn([
                'exception' => [
                    'env'               => 'dev',
                    'default_displayer' => HtmlDisplayer::class,
                    'template_path'     => __DIR__ . '/../../Resources/error.html',
                    'debug'             => false,
                ],
            ]);
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(RepositoryContract::class)
            ->andReturn($config);
        $container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(ExceptionIdentifier::class)
            ->andReturn(new ExceptionIdentifier());
        $container->shouldReceive('get')
            ->with(ResponseFactoryInterface::class)
            ->andReturn($this->mock(ResponseFactoryInterface::class));
        $container->shouldReceive('get')
            ->with(ServerRequestInterface::class)
            ->andReturn($this->mock(ServerRequestInterface::class));
        $container->shouldReceive('get')
            ->with(StreamFactoryInterface::class)
            ->andReturn($this->mock(StreamFactoryInterface::class));
        $container->shouldReceive('get')
            ->with(ExceptionInfo::class)
            ->andReturn($this->mock(ExceptionInfo::class));

        return $container;
    }
}
