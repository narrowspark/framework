<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Filter;

use Exception;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonApiDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\Exception\Filter\ContentTypeFilter;
use Viserio\Component\HttpFactory\ResponseFactory;

class ContentTypeFilterTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Psr\Http\Message\ServerRequestInterface
     */
    private $serverRequest;

    /**
     * @var \Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer
     */
    private $whoopsDisplayer;

    /**
     * @var \Viserio\Component\Exception\Displayer\JsonDisplayer
     */
    private $jsonDisplayer;

    /**
     * @var \Viserio\Component\Exception\Displayer\JsonApiDisplayer
     */
    private $jsonApiDisplayer;

    /**
     * @var \Viserio\Component\Exception\Displayer\HtmlDisplayer
     */
    private $htmlDisplayer;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $response               = new ResponseFactory();
        $this->serverRequest    = $this->mock(ServerRequestInterface::class);
        $this->whoopsDisplayer  = new WhoopsPrettyDisplayer($response);
        $this->jsonDisplayer    = new JsonDisplayer($response);
        $this->jsonApiDisplayer = new JsonApiDisplayer($response);
        $this->htmlDisplayer    = new HtmlDisplayer($response);
    }

    public function testAcceptAll(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('*/*');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json], $this->serverRequest);

        $this->assertSame([$debug, $html, $json], $displayers);
    }

    public function testAcceptHtmlAndAll(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('text/html,*/*');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json], $this->serverRequest);

        $this->assertSame([$debug, $html, $json], $displayers);
    }

    public function testAcceptJustHtml(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('text/html');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json], $this->serverRequest);

        $this->assertSame([$debug, $html], $displayers);
    }

    public function testAcceptText(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('text/*');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json], $this->serverRequest);

        $this->assertSame([$debug, $html], $displayers);
    }

    public function testAcceptJsonAndAll(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('application/json, */*');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json], $this->serverRequest);

        $this->assertSame([$debug, $html, $json], $displayers);
    }

    public function testAcceptJustJson(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('application/json');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json], $this->serverRequest);

        $this->assertSame([$json], $displayers);
    }

    public function testAcceptApplication(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('application/*');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;
        $api   = $this->jsonApiDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json, $api], $this->serverRequest);

        $this->assertSame([$json, $api], $displayers);
    }

    public function testAcceptComplexJson(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('application/foo+json');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;
        $api   = $this->jsonApiDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json, $api], $this->serverRequest);

        $this->assertSame([], $displayers);
    }

    public function testAcceptJsonApi(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('application/vnd.api+json');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;
        $api   = $this->jsonApiDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json, $api], $this->serverRequest);

        $this->assertSame([$api], $displayers);
    }

    public function testAcceptManyThings(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('text/*,application/foo+xml');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json], $this->serverRequest);

        $this->assertSame([$debug, $html], $displayers);
    }

    public function testAcceptNothing(): void
    {
        $this->serverRequest->shouldReceive('getHeaderLine')
            ->with('Accept')
            ->andReturn('application/xml');

        $debug = $this->whoopsDisplayer;
        $html  = $this->htmlDisplayer;
        $json  = $this->jsonDisplayer;

        $displayers = $this->arrangeContentTypeFilter([$debug, $html, $json], $this->serverRequest);

        $this->assertSame([], $displayers);
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    /**
     * @param array                                    $displayers
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    private function arrangeContentTypeFilter(array $displayers, ServerRequestInterface $request): array
    {
        $exception = new Exception();

        return (new ContentTypeFilter())->filter($displayers, $request, $exception, $exception, 500);
    }
}
