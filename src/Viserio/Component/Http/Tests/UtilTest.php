<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;
use Viserio\Component\Http\UploadedFile;
use Viserio\Component\Http\Util;

class UtilTest extends TestCase
{
    public function testCopiesToString()
    {
        $body   = 'foobaz';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $s = new Stream($stream);
        self::assertEquals('foobaz', Util::copyToString($s));
        $s->seek(0);

        self::assertEquals('foo', Util::copyToString($s, 3));
        self::assertEquals('baz', Util::copyToString($s, 3));
        self::assertEquals('', Util::copyToString($s));
    }

    public function testCopiesToStringStopsWhenReadFails()
    {
        $body   = 'foobaz';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $s1 = new Stream($stream);
        $s1 = FnStream::decorate($s1, [
            'read' => function () {
                return '';
            },
        ]);
        $result = Util::copyToString($s1);

        self::assertEquals('', $result);
    }

    public function testCopiesToStream()
    {
        $body   = 'foobaz';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $s1 = new Stream($stream);
        $s2 = new Stream(fopen('php://temp', 'r+'));
        Util::copyToStream($s1, $s2);
        self::assertEquals('foobaz', (string) $s2);

        $s2 = new Stream(fopen('php://temp', 'r+'));
        $s1->seek(0);

        Util::copyToStream($s1, $s2, 3);
        self::assertEquals('foo', (string) $s2);

        Util::copyToStream($s1, $s2, 3);
        self::assertEquals('foobaz', (string) $s2);
    }

    public function testCopyToStreamReadsInChunksInsteadOfAllInMemory()
    {
        $sizes = [];

        $s1 = new FnStream([
            'eof' => function () {
                return false;
            },
            'read' => function ($size) use (&$sizes) {
                $sizes[] = $size;

                return str_repeat('.', $size);
            },
        ]);

        $s2 = new Stream(fopen('php://temp', 'r+'));

        Util::copyToStream($s1, $s2, 16394);
        $s2->seek(0);

        self::assertEquals(16394, mb_strlen($s2->getContents()));
        self::assertEquals(8192, $sizes[0]);
        self::assertEquals(8192, $sizes[1]);
        self::assertEquals(10, $sizes[2]);
    }

    public function testStopsCopyToStreamWhenWriteFails()
    {
        $body   = 'foobaz';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $s1 = new Stream($stream);
        $s2 = new Stream(fopen('php://temp', 'r+'));
        $s2 = FnStream::decorate($s2, ['write' => function () {
            return 0;
        }]);
        Util::copyToStream($s1, $s2);

        self::assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenWriteFailsWithMaxLen()
    {
        $body   = 'foobaz';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $s1 = new Stream($stream);
        $s2 = new Stream(fopen('php://temp', 'r+'));
        $s2 = FnStream::decorate($s2, ['write' => function () {
            return 0;
        }]);

        Util::copyToStream($s1, $s2, 10);
        self::assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenReadFailsWithMaxLen()
    {
        $body   = 'foobaz';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $s1 = new Stream($stream);
        $s1 = FnStream::decorate($s1, ['read' => function () {
            return '';
        }]);
        $s2 = new Stream(fopen('php://temp', 'r+'));

        Util::copyToStream($s1, $s2, 10);
        self::assertEquals('', (string) $s2);
    }

    public function testOpensFilesSuccessfully()
    {
        $r = Util::tryFopen(__FILE__, 'r');
        self::assertInternalType('resource', $r);
        fclose($r);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to open [/path/to/does/not/exist] using mode r
     */
    public function testThrowsExceptionNotWarning()
    {
        Util::tryFopen('/path/to/does/not/exist', 'r');
    }
}
