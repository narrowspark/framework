<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests\Displayers;

use Exception;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\View\Factory;
use Viserio\Contracts\View\View;
use Viserio\Exception\Displayers\ViewDisplayer;
use Viserio\Exception\ExceptionInfo;

class ViewDisplayerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testError()
    {
        $factory = $this->mock(Factory::class);
        $view    = $this->mock(View::class);
        $view
            ->shouldReceive('__toString')
            ->once()
            ->andReturn("The server was acting as a gateway or proxy and received an invalid response from the upstream server.\n");
        $factory
            ->shouldReceive('create')
            ->once()
            ->with(
                'errors.502',
                ['id' => 'foo', 'code' => 502, 'name' => 'Bad Gateway', 'detail' => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.', 'summary' => 'Houston, We Have A Problem.']
            )
            ->andReturn($view);
        $displayer = new ViewDisplayer(new ExceptionInfo(), $factory);

        $response = $displayer->display(new Exception(), 'foo', 502, []);

        self::assertSame(
            "The server was acting as a gateway or proxy and received an invalid response from the upstream server.\n",
            (string) $response->getBody()
        );
        self::assertSame(502, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testPropertiesTrue()
    {
        $factory = $this->mock(Factory::class);
        $factory
            ->shouldReceive('exists')
            ->once()
            ->with('errors.500')
            ->andReturn(true);
        $displayer = new ViewDisplayer(new ExceptionInfo(), $factory);
        $exception = new Exception();

        self::assertFalse($displayer->isVerbose());
        self::assertTrue($displayer->canDisplay($exception, $exception, 500));
        self::assertSame('text/html', $displayer->contentType());
    }

    public function testPropertiesFalse()
    {
        $factory = $this->mock(Factory::class);
        $factory
            ->shouldReceive('exists')
            ->once()
            ->with('errors.500')
            ->andReturn(false);
        $displayer = new ViewDisplayer(new ExceptionInfo(), $factory);
        $exception = new Exception();

        self::assertFalse($displayer->isVerbose());
        self::assertFalse($displayer->canDisplay($exception, $exception, 500));
        self::assertSame('text/html', $displayer->contentType());
    }
}
