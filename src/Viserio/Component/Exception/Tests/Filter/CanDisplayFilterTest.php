<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Filter;

use Exception;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Filter\CanDisplayFilter;

class CanDisplayFilterTest extends MockeryTestCase
{
    public function testFirstIsRemoved(): void
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

    public function testNoChange(): void
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
