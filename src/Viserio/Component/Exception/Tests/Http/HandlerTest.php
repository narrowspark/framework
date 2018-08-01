<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Http;

use ErrorException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Http\Handler;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class HandlerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Psr\Http\Message\ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var array
     */
    private $config;

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->responseFactory = $this->mock(ResponseFactoryInterface::class);
        $this->logger          = $this->mock(LoggerInterface::class);

        $this->config = [
            'viserio' => [
                'exception' => [
                    'env'               => 'dev',
                    'default_displayer' => HtmlDisplayer::class,
                    'template_path'     => __DIR__ . '/../../Resource/error.html',
                    'debug'             => false,
                ],
            ],
        ];

        $this->handler = new Handler($this->config, $this->responseFactory, $this->logger);
    }

    public function testAddAndGetDisplayer(): void
    {
        $repsonseFactory = new ResponseFactory();

        $this->handler->addDisplayer(new HtmlDisplayer($repsonseFactory, $this->config));
        $this->handler->addDisplayer(new JsonDisplayer($repsonseFactory));
        $this->handler->addDisplayer(new JsonDisplayer($repsonseFactory));
        $this->handler->addDisplayer(new WhoopsPrettyDisplayer($repsonseFactory));

        static::assertCount(7, $this->handler->getDisplayers());
    }

    public function testAddAndGetTransformer(): void
    {
        $this->handler->addTransformer(new UndefinedMethodFatalErrorTransformer());
        $this->handler->addTransformer(new UndefinedMethodFatalErrorTransformer());

        static::assertCount(3, $this->handler->getTransformers());
    }

    public function testAddAndGetFilter(): void
    {
        $this->handler->addFilter(new VerboseFilter($this->config));
        $this->handler->addFilter(new VerboseFilter($this->config));

        static::assertCount(3, $this->handler->getFilters());
    }

    public function testHandleError(): void
    {
        try {
            $this->handler->handleError(\E_PARSE, 'test', '', 0);
        } catch (ErrorException $e) {
            static::assertInstanceOf(ErrorException::class, $e);
        }
    }
}
