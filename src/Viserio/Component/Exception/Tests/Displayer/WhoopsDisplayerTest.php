<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\WhoopsDisplayer;

class WhoopsDisplayerTest extends TestCase
{
    public function testServerError()
    {
        $displayer = new WhoopsDisplayer();
        $response  = $displayer->display(new Exception(), 'foo', 503, []);

        self::assertInternalType('string', (string) $response->getBody());
        self::assertSame(503, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError()
    {
        $displayer = new WhoopsDisplayer();
        $response  = $displayer->display(new Exception(), 'bar', 403, []);

        self::assertInternalType('string', (string) $response->getBody());
        self::assertSame(403, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties()
    {
        $exception = new Exception();
        $displayer = new WhoopsDisplayer();

        self::assertTrue($displayer->isVerbose());
        self::assertTrue($displayer->canDisplay($exception, $exception, 500));
        self::assertSame('text/html', $displayer->contentType());
    }
}
