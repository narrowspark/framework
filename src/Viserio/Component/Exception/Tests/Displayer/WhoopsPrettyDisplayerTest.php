<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

class WhoopsDisplayerTest extends TestCase
{
    /**
     * @var \Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer
     */
    private $whoops;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->whoops = new WhoopsPrettyDisplayer(new ResponseFactory());
    }

    public function testServerError(): void
    {
        $response = $this->whoops->display(new Exception(), 'foo', 503, []);

        self::assertInternalType('string', (string) $response->getBody());
        self::assertSame(503, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->whoops->display(new Exception(), 'bar', 403, []);

        self::assertInternalType('string', (string) $response->getBody());
        self::assertSame(403, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        $exception = new Exception();
        $displayer = $this->whoops;

        self::assertTrue($displayer->isVerbose());
        self::assertTrue($displayer->canDisplay($exception, $exception, 500));
        self::assertSame('text/html', $displayer->getContentType());
    }
}
