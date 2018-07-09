<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\WhoopsJsonDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class WhoopsJsonDisplayerTest extends TestCase
{
    /**
     * @var \Viserio\Component\Exception\Displayer\WhoopsJsonDisplayer
     */
    private $whoops;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->whoops = new WhoopsJsonDisplayer(new ResponseFactory());
    }

    public function testServerError(): void
    {
        $response = $this->whoops->display(new Exception(), 'foo', 503, []);

        static::assertInternalType('string', (string) $response->getBody());
        static::assertSame(503, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->whoops->display(new Exception(), 'bar', 403, []);

        static::assertInternalType('string', (string) $response->getBody());
        static::assertSame(403, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        $exception = new Exception();
        $displayer = $this->whoops;

        static::assertTrue($displayer->isVerbose());
        static::assertTrue($displayer->canDisplay($exception, $exception, 500));
        static::assertSame('application/json', $displayer->getContentType());
    }
}
