<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Stream;

use Viserio\Http\Stream\LazyOpenStream;

class LazyOpenStreamTest extends \PHPUnit_Framework_TestCase
{
    private $fname;

    public function setup()
    {
        $this->fname = tempnam('/tmp', 'tfile');

        if (file_exists($this->fname)) {
            unlink($this->fname);
        }
    }

    public function tearDown()
    {
        if (file_exists($this->fname)) {
            unlink($this->fname);
        }
    }

    public function testOpensLazily()
    {
        $lazy = new LazyOpenStream($this->fname, 'w+');
        $lazy->write('foo');

        $this->assertInternalType('array', $lazy->getMetadata());
        $this->assertFileExists($this->fname);
        $this->assertEquals('foo', file_get_contents($this->fname));
        $this->assertEquals('foo', (string) $lazy);
    }

    public function testProxiesToFile()
    {
        file_put_contents($this->fname, 'foo');
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

    public function testDetachesUnderlyingStream()
    {
        file_put_contents($this->fname, 'foo');
        $lazy = new LazyOpenStream($this->fname, 'r');
        $r = $lazy->detach();

        $this->assertInternalType('resource', $r);
        fseek($r, 0);

        $this->assertEquals('foo', stream_get_contents($r));

        fclose($r);
    }
}
