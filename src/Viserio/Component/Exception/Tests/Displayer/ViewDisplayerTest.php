<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Exception\Displayer\ViewDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Contract\View\Factory;
use Viserio\Contract\View\View;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ViewDisplayerTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\View\Factory */
    private $factoryMock;

    /** @var \Viserio\Component\Exception\Displayer\ViewDisplayer */
    private $displayer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factoryMock = Mockery::mock(Factory::class);
        $this->displayer = new ViewDisplayer(new ResponseFactory(), $this->factoryMock);
    }

    public function testError(): void
    {
        $exception = new Exception();
        $view = Mockery::mock(View::class);
        $view->shouldReceive('with')
            ->once()
            ->with('exception', $exception);
        $view->shouldReceive('__toString')
            ->once()
            ->andReturn("The server was acting as a gateway or proxy and received an invalid response from the upstream server.\n");
        $this->factoryMock->shouldReceive('create')
            ->once()
            ->with(
                'errors.502',
                ['id' => 'foo', 'code' => 502, 'name' => 'Bad Gateway', 'detail' => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.']
            )
            ->andReturn($view);

        $response = $this->displayer->display($exception, 'foo', 502, []);

        self::assertSame(
            "The server was acting as a gateway or proxy and received an invalid response from the upstream server.\n",
            (string) $response->getBody()
        );
        self::assertSame(502, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testPropertiesTrue(): void
    {
        $this->factoryMock->shouldReceive('exists')
            ->once()
            ->with('errors.500')
            ->andReturn(true);

        $exception = new Exception();

        self::assertFalse($this->displayer->isVerbose());
        self::assertTrue($this->displayer->canDisplay($exception, $exception, 500));
        self::assertSame('text/html', $this->displayer->getContentType());
    }

    public function testPropertiesFalse(): void
    {
        $this->factoryMock->shouldReceive('exists')
            ->once()
            ->with('errors.500')
            ->andReturn(false);

        $exception = new Exception();

        self::assertFalse($this->displayer->isVerbose());
        self::assertFalse($this->displayer->canDisplay($exception, $exception, 500));
        self::assertSame('text/html', $this->displayer->getContentType());
    }
}
