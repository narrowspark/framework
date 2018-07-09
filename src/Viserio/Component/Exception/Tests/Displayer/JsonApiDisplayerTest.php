<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\JsonApiDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class JsonApiDisplayerTest extends TestCase
{
    /**
     * @var \Viserio\Component\Exception\Displayer\JsonApiDisplayer
     */
    private $displayer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->displayer = new JsonApiDisplayer(new ResponseFactory());
    }

    public function testServerError(): void
    {
        $response = $this->displayer->display(new Exception(), 'foo', 500, []);
        $expected = '{"errors":[{"id":"foo","status":500,"title":"Internal Server Error","detail":"An error has occurred and this resource cannot be displayed."}]}';

        static::assertSame(\trim($expected), (string) $response->getBody());
        static::assertSame(500, $response->getStatusCode());
        static::assertSame('application/vnd.api+json', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->displayer->display(new Exception(), 'bar', 401, []);
        $expected = '{"errors":[{"id":"bar","status":401,"title":"Unauthorized","detail":"Authentication is required and has failed or has not yet been provided."}]}';

        static::assertSame(\trim($expected), (string) $response->getBody());
        static::assertSame(401, $response->getStatusCode());
        static::assertSame('application/vnd.api+json', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        static::assertFalse($this->displayer->isVerbose());
        static::assertTrue($this->displayer->canDisplay(new InvalidArgumentException(), new Exception('error', 500), 500));
        static::assertSame('application/vnd.api+json', $this->displayer->getContentType());
    }
}
