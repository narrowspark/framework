<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests\Filters;

use Exception;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\RequestInterface;
use Viserio\Exception\{
    ExceptionInfo,
    Displayers\HtmlDisplayer,
    Displayers\JsonDisplayer,
    Displayers\WhoopsDisplayer,
    Filters\VerboseFilter
};

class VerboseFilterTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testDebugStaysOnTop()
    {
        $request = $this->mock(RequestInterface::class);
        $exception = new Exception();
        $verbose = new WhoopsDisplayer();
        $standard = new JsonDisplayer(new ExceptionInfo());
        $displayers = (new VerboseFilter(true))->filter([$verbose, $standard], $request, $exception, $exception, 500);

        $this->assertSame([$verbose, $standard], $displayers);
    }

    public function testDebugIsRemoved()
    {
        $request = $this->mock(RequestInterface::class);
        $exception = new Exception();
        $verbose = new WhoopsDisplayer();
        $standard = new JsonDisplayer(new ExceptionInfo());
        $displayers = (new VerboseFilter(false))->filter([$verbose, $standard], $request, $exception, $exception, 500);

        $this->assertSame([$standard], $displayers);
    }

    public function testNoChangeInDebugMode()
    {
        $request = $this->mock(RequestInterface::class);
        $exception = new Exception();
        $json = new JsonDisplayer(new ExceptionInfo());
        $html = new HtmlDisplayer(new ExceptionInfo(), 'foo');
        $displayers = (new VerboseFilter(true))->filter([$json, $html], $request, $exception, $exception, 500);

        $this->assertSame([$json, $html], $displayers);
    }

    public function testNoChangeNotInDebugMode()
    {
        $request = $this->mock(RequestInterface::class);
        $exception = new Exception();
        $json = new JsonDisplayer(new ExceptionInfo());
        $displayers = (new VerboseFilter(false))->filter([$json], $request, $exception, $exception, 500);

        $this->assertSame([$json], $displayers);
    }
}
