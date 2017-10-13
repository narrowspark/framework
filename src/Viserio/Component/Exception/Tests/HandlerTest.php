<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use ErrorException;
use Exception;
use Interop\Http\Factory\ResponseFactoryInterface;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Handler;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\HttpFactory\ResponseFactory;

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

        $this->responseFactory = $this->mock(ResponseFactoryInterface::class);
        $this->loggger         = $this->mock(LoggerInterface::class);

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

        $this->handler = new Handler($this->container, $this->responseFactory, $this->loggger);
    }

    public function testAddAndGetDisplayer(): void
    {
        $info            = new ExceptionInfo();
        $repsonseFactory = new ResponseFactory();

        $this->handler->addDisplayer(new HtmlDisplayer($info, $repsonseFactory, $this->container));
        $this->handler->addDisplayer(new JsonDisplayer($info, $repsonseFactory));
        $this->handler->addDisplayer(new JsonDisplayer($info, $repsonseFactory));
        $this->handler->addDisplayer(new WhoopsDisplayer($repsonseFactory));

        self::assertCount(6, $this->handler->getDisplayers());
    }

    public function testAddAndGetTransformer(): void
    {
        $this->handler->addTransformer(new UndefinedMethodFatalErrorTransformer());
        $this->handler->addTransformer(new UndefinedMethodFatalErrorTransformer());

        self::assertCount(3, $this->handler->getTransformers());
    }

    public function testAddAndGetFilter(): void
    {
        $this->handler->addFilter(new VerboseFilter($this->container));
        $this->handler->addFilter(new VerboseFilter($this->container));

        self::assertCount(3, $this->handler->getFilters());
    }

    public function testReportError(): void
    {
        $exception = new Exception('Exception message');

        $this->loggger->shouldReceive('error')
            ->once()
            ->withArgs(['Exception message', Mockery::hasKey('exception')]);
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

    public function testHandleError(): void
    {
        try {
            $this->handler->handleError(E_PARSE, 'test', '', 0, null);
        } catch (ErrorException $e) {
            self::assertInstanceOf(ErrorException::class, $e);
        }
    }
}
