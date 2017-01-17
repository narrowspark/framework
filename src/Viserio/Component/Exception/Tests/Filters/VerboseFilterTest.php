<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Filters;

use Exception;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Viserio\Component\Exception\Displayers\HtmlDisplayer;
use Viserio\Component\Exception\Displayers\JsonDisplayer;
use Viserio\Component\Exception\Displayers\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filters\VerboseFilter;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;

class VerboseFilterTest extends TestCase
{
    use MockeryTrait;

    public function testDebugStaysOnTop()
    {
        $request    = $this->mock(RequestInterface::class);
        $exception  = new Exception();
        $verbose    = new WhoopsDisplayer();
        $standard   = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());
        $displayers = (new VerboseFilter(true))->filter([$verbose, $standard], $request, $exception, $exception, 500);

        self::assertSame([$verbose, $standard], $displayers);
    }

    public function testDebugIsRemoved()
    {
        $request    = $this->mock(RequestInterface::class);
        $exception  = new Exception();
        $verbose    = new WhoopsDisplayer();
        $standard   = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());
        $displayers = (new VerboseFilter(false))->filter([$verbose, $standard], $request, $exception, $exception, 500);

        self::assertSame([$standard], $displayers);
    }

    public function testNoChangeInDebugMode()
    {
        $request    = $this->mock(RequestInterface::class);
        $exception  = new Exception();
        $json       = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());
        $html       = new HtmlDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory(), 'foo');
        $displayers = (new VerboseFilter(true))->filter([$json, $html], $request, $exception, $exception, 500);

        self::assertSame([$json, $html], $displayers);
    }

    public function testNoChangeNotInDebugMode()
    {
        $request    = $this->mock(RequestInterface::class);
        $exception  = new Exception();
        $json       = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());
        $displayers = (new VerboseFilter(false))->filter([$json], $request, $exception, $exception, 500);

        self::assertSame([$json], $displayers);
    }
}
