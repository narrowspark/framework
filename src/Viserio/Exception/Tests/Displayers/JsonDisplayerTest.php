<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests\Displayers;

use Exception;
use InvalidArgumentException;
use Viserio\Exception\Displayers\JsonDisplayer;
use Viserio\Exception\ExceptionInfo;

class JsonDisplayerTest extends \PHPUnit_Framework_TestCase
{
    public function testServerError()
    {
        $displayer = new JsonDisplayer(new ExceptionInfo());

        $response = $displayer->display(new Exception(), 'foo', 500, []);
        $expected = '{"errors":[{"id":"foo","status":500,"title":"Internal Server Error","detail":"An error has occurred and this resource cannot be displayed."}]}';

        $this->assertSame(trim($expected), (string) $response->getBody());
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError()
    {
        $displayer = new JsonDisplayer(new ExceptionInfo());

        $response = $displayer->display(new Exception(), 'bar', 401, []);
        $expected = '{"errors":[{"id":"bar","status":401,"title":"Unauthorized","detail":"Authentication is required and has failed or has not yet been provided."}]}';

        $this->assertSame(trim($expected), (string) $response->getBody());
        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties()
    {
        $displayer = new JsonDisplayer(new ExceptionInfo());

        $this->assertFalse($displayer->isVerbose());
        $this->assertTrue($displayer->canDisplay(new InvalidArgumentException(), new Exception('error', 500), 500));
        $this->assertSame('application/json', $displayer->contentType());
    }
}
