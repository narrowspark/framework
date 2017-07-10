<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\HttpFactory\ResponseFactory;

class JsonDisplayerTest extends TestCase
{
    /**
     * @var \Viserio\Component\Exception\Displayer\JsonDisplayer
     */
    private $displayer;

    public function setUp()
    {
        $this->displayer = new JsonDisplayer(new ExceptionInfo(), new ResponseFactory());
    }

    public function testServerError(): void
    {
        $response = $this->displayer->display(new Exception(), 'foo', 500, []);
        $expected = '{"errors":[{"id":"foo","status":500,"title":"Internal Server Error","detail":"An error has occurred and this resource cannot be displayed."}]}';

        self::assertSame(\trim($expected), (string) $response->getBody());
        self::assertSame(500, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->displayer->display(new Exception(), 'bar', 401, []);
        $expected = '{"errors":[{"id":"bar","status":401,"title":"Unauthorized","detail":"Authentication is required and has failed or has not yet been provided."}]}';

        self::assertSame(\trim($expected), (string) $response->getBody());
        self::assertSame(401, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        self::assertFalse($this->displayer->isVerbose());
        self::assertTrue($this->displayer->canDisplay(new InvalidArgumentException(), new Exception('error', 500), 500));
        self::assertSame('application/json', $this->displayer->contentType());
    }
}
