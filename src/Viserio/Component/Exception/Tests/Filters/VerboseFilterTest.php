<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Filters;

use Exception;
use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
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
        $request    = $this->mock(ServerRequestInterface::class);
        $exception  = new Exception();
        $verbose    = new WhoopsDisplayer();
        $standard   = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());
        $displayers = (new VerboseFilter($this->getContainer(true)))->filter([$verbose, $standard], $request, $exception, $exception, 500);

        self::assertSame([$verbose, $standard], $displayers);
    }

    public function testDebugIsRemoved()
    {
        $request    = $this->mock(ServerRequestInterface::class);
        $exception  = new Exception();
        $verbose    = new WhoopsDisplayer();
        $standard   = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());
        $displayers = (new VerboseFilter($this->getContainer()))->filter([$verbose, $standard], $request, $exception, $exception, 500);

        self::assertSame([$standard], $displayers);
    }

    public function testNoChangeInDebugMode()
    {
        $request    = $this->mock(ServerRequestInterface::class);
        $exception  = new Exception();
        $json       = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());
        $html       = new HtmlDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory(), $this->getContainer());
        $displayers = (new VerboseFilter($this->getContainer(true)))->filter([$json, $html], $request, $exception, $exception, 500);

        self::assertSame([$json, $html], $displayers);
    }

    public function testNoChangeNotInDebugMode()
    {
        $request    = $this->mock(ServerRequestInterface::class);
        $exception  = new Exception();
        $json       = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());
        $displayers = (new VerboseFilter($this->getContainer()))->filter([$json], $request, $exception, $exception, 500);

        self::assertSame([$json], $displayers);
    }

    private function getContainer(bool $debug = false)
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'exception' => [
                    'template_path' => __DIR__ . '/../../Resources/error.html',
                    'debug'         => $debug,
                ],
            ]);

        return new ArrayContainer([
            RepositoryContract::class       => $config,
            ExceptionInfo::class            => new ExceptionInfo(),
            ResponseFactoryInterface::class => new ResponseFactory(),
            StreamFactoryInterface::class   => new StreamFactory(),
        ]);
    }
}
