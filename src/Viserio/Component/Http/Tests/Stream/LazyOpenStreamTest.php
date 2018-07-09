<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream\LazyOpenStream;

/**
 * @internal
 */
final class LazyOpenStreamTest extends TestCase
{
    private $fname;

    protected function setup(): void
    {
        \mkdir(__DIR__ . '/tmp');

        $this->fname = \tempnam(__DIR__ . '/tmp', 'tfile');
    }

    protected function tearDown(): void
    {
        if (\file_exists($this->fname)) {
            \unlink($this->fname);
        }

        \rmdir(__DIR__ . '/tmp');

        parent::tearDown();
    }

    public function testOpensLazily(): void
    {
        $lazy = new LazyOpenStream($this->fname, 'w+');
        $lazy->write('foo');

        static::assertInternalType('array', $lazy->getMetadata());
        static::assertFileExists($this->fname);
        static::assertStringEqualsFile($this->fname, 'foo');
        static::assertEquals('foo', (string) $lazy);
    }

    public function testProxiesToFile(): void
    {
        \file_put_contents($this->fname, 'foo');
        $lazy = new LazyOpenStream($this->fname, 'r');

        static::assertEquals('foo', $lazy->read(4));
        static::assertTrue($lazy->eof());
        static::assertEquals(3, $lazy->tell());
        static::assertTrue($lazy->isReadable());
        static::assertTrue($lazy->isSeekable());
        static::assertFalse($lazy->isWritable());

        $lazy->seek(1);

        static::assertEquals('oo', $lazy->getContents());
        static::assertEquals('foo', (string) $lazy);
        static::assertEquals(3, $lazy->getSize());
        static::assertInternalType('array', $lazy->getMetadata());

        $lazy->close();
    }

    public function testDetachesUnderlyingStream(): void
    {
        \file_put_contents($this->fname, 'foo');
        $lazy = new LazyOpenStream($this->fname, 'r');
        $r    = $lazy->detach();

        static::assertInternalType('resource', $r);
        \fseek($r, 0);

        static::assertEquals('foo', \stream_get_contents($r));

        \fclose($r);
    }
}
