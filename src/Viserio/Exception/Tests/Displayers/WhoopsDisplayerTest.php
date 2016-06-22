<?php
namespace Viserio\Exception\Tests\Displayers;

use Exception;
use Viserio\Exception\Displayers\WhoopsDisplayer;

class WhoopsDisplayerTest extends \PHPUnit_Framework_TestCase
{
    public function testServerError()
    {
        $displayer = new WhoopsDisplayer();
        $response = $displayer->display(new Exception(), 'foo', 503, []);

        $this->assertInternalType('string', (string) $response->getBody());
        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError()
    {
        $displayer = new WhoopsDisplayer();
        $response = $displayer->display(new Exception(), 'bar', 403, []);

        $this->assertInternalType('string', (string) $response->getBody());
        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties()
    {
        $exception = new Exception();
        $displayer = new WhoopsDisplayer();

        $this->assertTrue($displayer->isVerbose());
        $this->assertTrue($displayer->canDisplay($exception, $exception, 500));
        $this->assertSame('text/html', $displayer->contentType());
    }
}
