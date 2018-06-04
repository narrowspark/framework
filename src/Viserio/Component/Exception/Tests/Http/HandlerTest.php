<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Http;

use ErrorException;
use Interop\Http\Factory\ResponseFactoryInterface;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Http\Handler;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\HttpFactory\ResponseFactory;

class HandlerTest extends MockeryTestCase
{
    /**
     * @var \Interop\Http\Factory\ResponseFactoryInterface|\Mockery\MockInterface
     */
    private $responseFactory;

    /**
     * @var \Mockery\MockInterface|\Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \Mockery\MockInterface|\Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Viserio\Component\Exception\Http\Handler
     */
    private $handler;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = $this->mock(ResponseFactoryInterface::class);
        $this->logger          = $this->mock(LoggerInterface::class);

        $config = [
            'viserio' => [
                'exception' => [
                    'env'               => 'dev',
                    'default_displayer' => HtmlDisplayer::class,
                    'template_path'     => __DIR__ . '/../../Resource/error.html',
                    'debug'             => false,
                ],
            ],
        ];
        $this->container = new ArrayContainer(['config' => $config]);

        $this->handler = new Handler($this->container, $this->responseFactory, $this->logger);
    }

    public function testAddAndGetDisplayer(): void
    {
        $repsonseFactory = new ResponseFactory();

        $this->handler->addDisplayer(new HtmlDisplayer($repsonseFactory, $this->container));
        $this->handler->addDisplayer(new JsonDisplayer($repsonseFactory));
        $this->handler->addDisplayer(new JsonDisplayer($repsonseFactory));
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
}
