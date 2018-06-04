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

        $this->assertInternalType('array', $lazy->getMetadata());
        $this->assertFileExists($this->fname);
        $this->assertStringEqualsFile($this->fname, 'foo');
        $this->assertEquals('foo', (string) $lazy);
    }

    public function testProxiesToFile(): void
    {
        \file_put_contents($this->fname, 'foo');
        $lazy = new LazyOpenStream($this->fname, 'r');

        $this->assertEquals('foo', $lazy->read(4));
        $this->assertTrue($lazy->eof());
        $this->assertEquals(3, $lazy->tell());
        $this->assertTrue($lazy->isReadable());
        $this->assertTrue($lazy->isSeekable());
        $this->assertFalse($lazy->isWritable());

        $lazy->seek(1);

        $this->assertEquals('oo', $lazy->getContents());
        $this->assertEquals('foo', (string) $lazy);
        $this->assertEquals(3, $lazy->getSize());
        $this->assertInternalType('array', $lazy->getMetadata());

        $lazy->close();
    }

    public function testDetachesUnderlyingStream(): void
    {
        \file_put_contents($this->fname, 'foo');
        $lazy = new LazyOpenStream($this->fname, 'r');
        $r    = $lazy->detach();

        $this->assertInternalType('resource', $r);
        \fseek($r, 0);

        $this->assertEquals('foo', \stream_get_contents($r));

        \fclose($r);
    }
}
