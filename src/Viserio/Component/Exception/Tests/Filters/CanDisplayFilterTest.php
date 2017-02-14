<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Filters;

use Exception;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Exception\Displayers\HtmlDisplayer;
use Viserio\Component\Exception\Displayers\JsonDisplayer;
use Viserio\Component\Exception\Filters\CanDisplayFilter;

class CanDisplayFilterTest extends TestCase
{
    use MockeryTrait;

    public function testFirstIsRemoved()
    {
        $request   = $this->mock(ServerRequestInterface::class);
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

        self::assertSame([$json], $displayers);
    }

    public function testNoChange()
    {
        $request   = $this->mock(ServerRequestInterface::class);
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

        self::assertSame([$html, $json], $displayers);
    }
}
