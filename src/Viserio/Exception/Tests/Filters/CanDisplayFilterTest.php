<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests\Filters;

use Exception;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\RequestInterface;
use Viserio\Exception\{
    Displayers\HtmlDisplayer,
    Displayers\JsonDisplayer,
    Filters\CanDisplayFilter
};

class CanDisplayFilterTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testFirstIsRemoved()
    {
        $request = $this->mock(RequestInterface::class);
        $exception = new Exception();

        $html = $this->mock(HtmlDisplayer::class);
        $html
            ->shouldReceive('canDisplay')
            ->once()
            ->with($exception, $exception, 500)
            ->andReturn(false);

        $json = $this->mock(JsonDisplayer::class);
        $json
            ->shouldReceive('canDisplay')
            ->once()
            ->with($exception, $exception, 500)
            ->andReturn(true);

        $displayers = (new CanDisplayFilter())->filter([$html, $json], $request, $exception, $exception, 500);

        $this->assertSame([$json], $displayers);
    }

    public function testNoChange()
    {
        $request = $this->mock(RequestInterface::class);
        $exception = new Exception();

        $html = $this->mock(HtmlDisplayer::class);
        $html
            ->shouldReceive('canDisplay')
            ->once()
            ->with($exception, $exception, 500)
            ->andReturn(true);

        $json = $this->mock(JsonDisplayer::class);
        $json
            ->shouldReceive('canDisplay')
            ->once()
            ->with($exception, $exception, 500)
            ->andReturn(true);

        $displayers = (new CanDisplayFilter())->filter([$html, $json], $request, $exception, $exception, 500);

        $this->assertSame([$html, $json], $displayers);
    }
}
