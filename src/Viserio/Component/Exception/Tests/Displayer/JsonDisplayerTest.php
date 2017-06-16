<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;

class JsonDisplayerTest extends TestCase
{
    public function testServerError()
    {
        $displayer = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());

        $response = $displayer->display(new Exception(), 'foo', 500, []);
        $expected = '{"errors":[{"id":"foo","status":500,"title":"Internal Server Error","detail":"An error has occurred and this resource cannot be displayed."}]}';

        self::assertSame(trim($expected), (string) $response->getBody());
        self::assertSame(500, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError()
    {
        $displayer = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());

        $response = $displayer->display(new Exception(), 'bar', 401, []);
        $expected = '{"errors":[{"id":"bar","status":401,"title":"Unauthorized","detail":"Authentication is required and has failed or has not yet been provided."}]}';

        self::assertSame(trim($expected), (string) $response->getBody());
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties()
    {
        $displayer = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory(), new StreamFactory());

        self::assertFalse($displayer->isVerbose());
        self::assertTrue($displayer->canDisplay(new InvalidArgumentException(), new Exception('error', 500), 500));
        self::assertSame('application/json', $displayer->contentType());
    }
}
