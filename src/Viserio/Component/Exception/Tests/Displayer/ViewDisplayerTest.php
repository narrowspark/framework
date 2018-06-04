<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\View\Factory;
use Viserio\Component\Contract\View\View;
use Viserio\Component\Exception\Displayer\ViewDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class ViewDisplayerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\View\Factory
     */
    private $factoryMock;

    /**
     * @var \Viserio\Component\Exception\Displayer\ViewDisplayer
     */
    private $displayer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factoryMock = $this->mock(Factory::class);
        $this->displayer   = new ViewDisplayer(new ResponseFactory(), $this->factoryMock);
    }

    public function testError(): void
    {
        $exception = new Exception();
        $view      = $this->mock(View::class);
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
                ['id' => 'foo', 'code' => 502, 'name' => 'Bad Gateway', 'detail' => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.', 'summary' => 'Houston, We Have A Problem.']
            )
            ->andReturn($view);

        $response = $this->displayer->display($exception, 'foo', 502, []);

        $this->assertSame(
            "The server was acting as a gateway or proxy and received an invalid response from the upstream server.\n",
            (string) $response->getBody()
        );
        $this->assertSame(502, $response->getStatusCode());
        $this->assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testPropertiesTrue(): void
    {
        $this->factoryMock->shouldReceive('exists')
            ->once()
            ->with('errors.500')
            ->andReturn(true);

        $exception = new Exception();

        $this->assertFalse($this->displayer->isVerbose());
        $this->assertTrue($this->displayer->canDisplay($exception, $exception, 500));
        $this->assertSame('text/html', $this->displayer->getContentType());
    }

    public function testPropertiesFalse(): void
    {
        $this->factoryMock->shouldReceive('exists')
            ->once()
            ->with('errors.500')
            ->andReturn(false);

        $exception = new Exception();

        $this->assertFalse($this->displayer->isVerbose());
        $this->assertFalse($this->displayer->canDisplay($exception, $exception, 500));
        $this->assertSame('text/html', $this->displayer->getContentType());
    }
}
