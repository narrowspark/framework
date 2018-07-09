<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Displayer\SymfonyDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class SymfonyDisplayerTest extends TestCase
{
    /**
     * @var \Viserio\Component\Exception\Displayer\SymfonyDisplayer
     */
    private $displayer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->displayer = new SymfonyDisplayer(new ResponseFactory());
    }

    public function testServerError(): void
    {
        $response = $this->displayer->display(new Exception(), 'foo', 500, []);

        static::assertSame(500, $response->getStatusCode());
        static::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->displayer->display(new Exception(), 'bar', 401, []);

        static::assertSame(401, $response->getStatusCode());
        static::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        static::assertTrue($this->displayer->isVerbose());
        static::assertTrue($this->displayer->canDisplay(new InvalidArgumentException(), new Exception('error', 500), 500));
        static::assertSame('text/html', $this->displayer->getContentType());
    }
}
