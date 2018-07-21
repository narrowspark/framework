<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Filter;

use Exception;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class VerboseFilterTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer
     */
    private $whoopsDisplayer;

    /**
     * @var \Viserio\Component\Exception\Displayer\JsonDisplayer
     */
    private $jsonDisplayer;

    /**
     * @var \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface
     */
    private $requestMock;

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $response              = new ResponseFactory();
        $this->whoopsDisplayer = new WhoopsPrettyDisplayer($response);
        $this->jsonDisplayer   = new JsonDisplayer($response);
        $this->requestMock     = $this->mock(ServerRequestInterface::class);
        $this->exception       = new Exception();
    }

    public function testDebugStaysOnTop(): void
    {
        $verbose    = $this->whoopsDisplayer;
        $standard   = $this->jsonDisplayer;
        $displayers = $this->arrangeVerboseFilter([$verbose, $standard], true);

        static::assertSame([$verbose, $standard], $displayers);
    }

    public function testDebugIsRemoved(): void
    {
        $verbose    = $this->whoopsDisplayer;
        $standard   = $this->jsonDisplayer;
        $displayers = $this->arrangeVerboseFilter([$verbose, $standard]);

        static::assertSame([$standard], $displayers);
    }

    public function testNoChangeInDebugMode(): void
    {
        $json       = $this->jsonDisplayer;
        $html       = new HtmlDisplayer(new ResponseFactory(), $this->getConfig());
        $displayers = $this->arrangeVerboseFilter([$json, $html], true);

        static::assertSame([$json, $html], $displayers);
    }

    public function testNoChangeNotInDebugMode(): void
    {
        $json       = $this->jsonDisplayer;
        $displayers = $this->arrangeVerboseFilter([$json], true);

        static::assertSame([$json], $displayers);
    }

    /**
     * @param bool $debug
     *
     * @return array
     */
    private function getConfig(bool $debug = false): array
    {
        return [
            'viserio' => [
                'exception' => [
                    'template_path' => __DIR__ . '/../../Resource/error.html',
                    'debug'         => $debug,
                ],
            ],
        ];
    }

    /**
     * @param array $displayers
     * @param bool  $debug
     *
     * @return array
     */
    private function arrangeVerboseFilter(array $displayers, bool $debug = false): array
    {
        return (new VerboseFilter($this->getConfig($debug)))->filter(
            $displayers,
            $this->requestMock,
            $this->exception,
            $this->exception,
            500
        );
    }
}
