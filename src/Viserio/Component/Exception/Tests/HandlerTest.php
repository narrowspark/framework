<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use Error;
use ErrorException;
use Interop\Http\Factory\ResponseFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Viserio\Component\Console\Output\SpyOutput;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Exception\Console\SymfonyConsoleOutput;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Handler;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\HttpFactory\ResponseFactory;

class HandlerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \Interop\Http\Factory\ResponseFactoryInterface|\Mockery\MockInterface
     */
    private $responseFactory;

    /**
     * @var \Mockery\MockInterface|\Psr\Log\LoggerInterface
     */
    private $loggger;

    /**
     * @var \Viserio\Component\Exception\Handler
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
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
        $this->handler->addDisplayer(new WhoopsPrettyDisplayer($repsonseFactory));

        self::assertCount(7, $this->handler->getDisplayers());
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

    public function testHandleError(): void
    {
        try {
            $this->handler->handleError(E_PARSE, 'test', '', 0);
        } catch (ErrorException $e) {
            self::assertInstanceOf(ErrorException::class, $e);
        }
    }

    public function testRenderForConsole(): void
    {
        $output = new SpyOutput();

        $this->handler->renderForConsole(new SymfonyConsoleOutput($output), new Error());

        $file = __FILE__;

        self::assertSame(
            "
Error : 

at $file : 122
118:     public function testRenderForConsole(): void
119:     {
120:         \$output = new SpyOutput();
121: 
122:         \$this->handler->renderForConsole(new SymfonyConsoleOutput(\$output), new Error());
123: 
124:         \$file = __FILE__;
125: 
126:         self::assertSame(
127:             \"

Exception trace:

1   Error::__construct(\"\")
    $file : 122

2   Viserio\Component\Exception\Tests\HandlerTest::testRenderForConsole()
    [internal] : 0


Please use the argument -v to see all trace.
",
            $output->output
        );
    }
}
