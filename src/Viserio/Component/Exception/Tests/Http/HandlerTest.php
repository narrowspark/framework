<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Exception\Tests\Http;

use ErrorException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Log\LoggerInterface;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\Exception\ExceptionIdentifier;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Http\Handler;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 *
 * @small
 */
final class HandlerTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Psr\Http\Message\ResponseFactoryInterface */
    private $responseFactoryMock;

    /** @var array */
    private $config;

    /** @var \Mockery\MockInterface|\Psr\Log\LoggerInterface */
    private $loggerMock;

    /** @var \Viserio\Component\Exception\Http\Handler */
    private $handler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->responseFactoryMock = \Mockery::mock(ResponseFactoryInterface::class);
        $this->loggerMock = \Mockery::mock(LoggerInterface::class);

        $this->config = [
            'viserio' => [
                'exception' => [
                    'env' => 'dev',
                    'default_displayer' => HtmlDisplayer::class,
                    'template_path' => \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'error.html',
                    'debug' => false,
                ],
            ],
        ];

        $this->handler = new Handler($this->config, $this->responseFactoryMock, $this->loggerMock);
    }

    public function testAddAndGetDisplayer(): void
    {
        $repsonseFactory = new ResponseFactory();

        $priority = 0;

        $this->handler->addDisplayer(new HtmlDisplayer($repsonseFactory, $this->config), $priority);
        $this->handler->addDisplayer(new JsonDisplayer($repsonseFactory), $priority);
        $this->handler->addDisplayer(new JsonDisplayer($repsonseFactory), $priority);
        $this->handler->addDisplayer(new WhoopsPrettyDisplayer($repsonseFactory), $priority);

        $displayers = $this->handler->getDisplayers();

        self::assertCount(3, $displayers[$priority]);
    }

    public function testAddAndGetTransformer(): void
    {
        $this->handler->addTransformer(new UndefinedMethodFatalErrorTransformer());
        $this->handler->addTransformer(new UndefinedMethodFatalErrorTransformer());

        self::assertCount(3, $this->handler->getTransformers());
    }

    public function testAddAndGetFilter(): void
    {
        $priority = 0;

        $this->handler->addFilter(new VerboseFilter($this->config), $priority);
        $this->handler->addFilter(new VerboseFilter($this->config), $priority);

        $filters = $this->handler->getFilters();

        self::assertCount(1, $filters[$priority]);
    }

    public function testHandleError(): void
    {
        try {
            $this->handler->handleError(\E_PARSE, 'test', '', 0);
        } catch (ErrorException $e) {
            self::assertInstanceOf(ErrorException::class, $e);
        }
    }

    public function testHandleException(): void
    {
        $displayer = new HtmlDisplayer(new ResponseFactory());

        $this->handler->addDisplayer($displayer);

        $exception = new \Exception('test');

        $this->handler->handleException($exception);

        $this->expectOutputString((string) $displayer->display($exception, ExceptionIdentifier::identify($exception), 500, [])->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
