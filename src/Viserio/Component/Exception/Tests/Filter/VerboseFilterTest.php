<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Filter;

use Exception;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\HttpFactory\ResponseFactory;

class VerboseFilterTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Exception\Displayer\WhoopsDisplayer
     */
    private $whoopsDisplayer;

    /**
     * @var \Viserio\Component\Exception\Displayer\JsonDisplayer
     */
    private $jsonDisplayer;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface|\Mockery\MockInterface
     */
    private $requestMock;

    /**
     * @var \Exception
     */
    private $exception;

    public function setUp()
    {
        $response              = new ResponseFactory();
        $this->whoopsDisplayer = new WhoopsDisplayer($response);
        $this->jsonDisplayer   = new JsonDisplayer(new ExceptionInfo(), $response);
        $this->requestMock     = $this->mock(ServerRequestInterface::class);
        $this->exception       = new Exception();
    }

    public function testDebugStaysOnTop(): void
    {
        $verbose    = $this->whoopsDisplayer;
        $standard   = $this->jsonDisplayer;
        $displayers = (new VerboseFilter($this->getContainer(true)))->filter(
            [$verbose, $standard],
            $this->requestMock,
            $this->exception,
            $this->exception,
            500
        );

        self::assertSame([$verbose, $standard], $displayers);
    }

    public function testDebugIsRemoved(): void
    {
        $verbose    = $this->whoopsDisplayer;
        $standard   = $this->jsonDisplayer;
        $displayers = (new VerboseFilter($this->getContainer()))->filter(
            [$verbose, $standard],
            $this->requestMock,
            $this->exception,
            $this->exception,
            500
        );

        self::assertSame([$standard], $displayers);
    }

    public function testNoChangeInDebugMode(): void
    {
        $json       = $this->jsonDisplayer;
        $html       = new HtmlDisplayer(new ExceptionInfo(), new ResponseFactory(), $this->getContainer());
        $displayers = (new VerboseFilter($this->getContainer(true)))->filter(
            [$json, $html],
            $this->requestMock,
            $this->exception,
            $this->exception,
            500
        );

        self::assertSame([$json, $html], $displayers);
    }

    public function testNoChangeNotInDebugMode(): void
    {
        $json       = $this->jsonDisplayer;
        $displayers = (new VerboseFilter($this->getContainer()))->filter(
            [$json],
            $this->requestMock,
            $this->exception,
            $this->exception,
            500
        );

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
            RepositoryContract::class => $config,
        ]);
    }
}
