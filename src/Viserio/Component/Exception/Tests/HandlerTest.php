<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use ErrorException;
use Exception;
use Interop\Http\Factory\ResponseFactoryInterface;
use Mockery;
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
    /**
     * @var \Psr\Container\ContainerInterface|\Mockery\MockInterface
     */
    private $container;

    /**
     * @var \Interop\Http\Factory\ResponseFactoryInterface|\Mockery\MockInterface
     */
    private $responseFactory;

    /**
     * @var \Psr\Container\ContainerInterface|\Mockery\MockInterface
     */
    private $serverRequest;

    public function setUp()
    {

        $this->responseFactory = $this->mock(ResponseFactoryInterface::class);
        $this->serverRequestFactory = $this->mock(ServerRequestInterface::class);
        $this->serverRequest = $this->mock(ServerRequestInterface::class);

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
        $this->container = $this->mock(ContainerInterface::class);
        $this->container->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $this->container->shouldReceive('get')
            ->with(RepositoryContract::class)
            ->andReturn($config);
        $this->container->shouldReceive('get')
            ->with(ExceptionIdentifier::class)
            ->andReturn(new ExceptionIdentifier());
        $this->container->shouldReceive('get')
            ->with(ExceptionInfo::class)
            ->andReturn(new ExceptionInfo());
    }

    public function testAddAndGetDisplayer(): void
    {
        $this->container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(false);
        $handler = new Handler($this->container, '', $this->responseFactory);

        $info = $this->container->get(ExceptionInfo::class);

        $handler->addDisplayer(new HtmlDisplayer($info, new ResponseFactory(), new StreamFactory(), $this->container));
        $handler->addDisplayer(new JsonDisplayer($info, new ResponseFactory(), new StreamFactory()));
        $handler->addDisplayer(new JsonDisplayer($info, new ResponseFactory(), new StreamFactory()));
        $handler->addDisplayer(new WhoopsDisplayer());

        self::assertCount(3, $handler->getDisplayers());
    }

    public function testAddAndGetTransformer(): void
    {
        $this->container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(false);
        $handler = new Handler($this->container);

        $handler->addTransformer(new CommandLineTransformer());
        $handler->addTransformer(new CommandLineTransformer());

        self::assertCount(1, $handler->getTransformers());
    }

    public function testAddAndGetFilter(): void
    {
        $this->container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(false);
        $handler = new Handler($this->container);

        $handler->addFilter(new VerboseFilter($this->container));
        $handler->addFilter(new VerboseFilter($this->container));

        self::assertCount(1, $handler->getFilters());
    }

    public function testReportError(): void
    {
        $exception = new Exception('Exception message');

        $this->container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(true);
        $log = $this->mock(LoggerInterface::class);
        $log->shouldReceive('error')
            ->once()
            ->withArgs(['Exception message', Mockery::hasKey('exception')]);
        $log->shouldReceive('critical')
            ->never();
        $this->container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);

        $handler = new Handler($this->container);

        $handler->report($exception);
    }

    public function testReportCritical(): void
    {
        $exception = new FatalThrowableError(new Exception());

        $this->container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(true);
        $log = $this->mock(LoggerInterface::class);
        $log->shouldReceive('error')
            ->never();
        $log->shouldReceive('critical')
            ->once();

        $this->container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);

        $handler = new Handler($this->container);

        $handler->report($exception);
    }

    public function testShouldntReport(): void
    {
        $exception = new FatalThrowableError(new Exception());

        $this->container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(true);
        $log = $this->mock(LoggerInterface::class);
        $log->shouldReceive('critical')
            ->never();

        $this->container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(true);
        $this->container->shouldReceive('get')
            ->with(LoggerInterface::class)
            ->andReturn($log);
        $handler = new Handler($this->container);

        $handler->addShouldntReport($exception);

        $handler->report($exception);
    }

    public function testHandleError(): void
    {
        $this->container->shouldReceive('has')
            ->with(LoggerInterface::class)
            ->andReturn(false);
        $handler = new Handler($this->container);

        try {
            $handler->handleError(E_PARSE, 'test', '', 0, null);
        } catch (ErrorException $e) {
            self::assertInstanceOf(ErrorException::class, $e);
        }
    }
}
