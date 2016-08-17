<?php
declare(strict_types=1);
namespace Viserio\Http\Tests;

use ArrayIterator;
use Viserio\Http\Stream;
use Viserio\Http\Stream\FnStream;
use Viserio\Http\Stream\PumpStream;
use Viserio\Http\Tests\Fixture\HasToString;
use Viserio\Http\UploadedFile;
use Viserio\Http\Util;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testCopiesToString()
    {
        $s = Util::getStream('foobaz');
        $this->assertEquals('foobaz', Util::copyToString($s));
        $s->seek(0);

        $this->assertEquals('foo', Util::copyToString($s, 3));
        $this->assertEquals('baz', Util::copyToString($s, 3));
        $this->assertEquals('', Util::copyToString($s));
    }

    public function testCopiesToStringStopsWhenReadFails()
    {
        $s1 = Util::getStream('foobaz');
        $s1 = FnStream::decorate($s1, [
            'read' => function () {
                return '';
            },
        ]);
        $result = Util::copyToString($s1);

        $this->assertEquals('', $result);
    }

    public function testCopiesToStream()
    {
        $s1 = Util::getStream('foobaz');
        $s2 = Util::getStream('');
        Util::copyToStream($s1, $s2);
        $this->assertEquals('foobaz', (string) $s2);

        $s2 = Util::getStream('');
        $s1->seek(0);

        Util::copyToStream($s1, $s2, 3);
        $this->assertEquals('foo', (string) $s2);

        Util::copyToStream($s1, $s2, 3);
        $this->assertEquals('foobaz', (string) $s2);
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

        $s2 = Util::getStream('');

        Util::copyToStream($s1, $s2, 16394);
        $s2->seek(0);

        $this->assertEquals(16394, strlen($s2->getContents()));
        $this->assertEquals(8192, $sizes[0]);
        $this->assertEquals(8192, $sizes[1]);
        $this->assertEquals(10, $sizes[2]);
    }

    public function testStopsCopyToStreamWhenWriteFails()
    {
        $s1 = Util::getStream('foobaz');
        $s2 = Util::getStream('');
        $s2 = FnStream::decorate($s2, ['write' => function () {
            return 0;
        }]);
        Util::copyToStream($s1, $s2);

        $this->assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenWriteFailsWithMaxLen()
    {
        $s1 = Util::getStream('foobaz');
        $s2 = Util::getStream('');
        $s2 = FnStream::decorate($s2, ['write' => function () {
            return 0;
        }]);

        Util::copyToStream($s1, $s2, 10);
        $this->assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenReadFailsWithMaxLen()
    {
        $s1 = Util::getStream('foobaz');
        $s1 = FnStream::decorate($s1, ['read' => function () {
            return '';
        }]);
        $s2 = Util::getStream('');

        Util::copyToStream($s1, $s2, 10);
        $this->assertEquals('', (string) $s2);
    }

    public function testOpensFilesSuccessfully()
    {
        $r = Util::tryFopen(__FILE__, 'r');
        $this->assertInternalType('resource', $r);
        fclose($r);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unable to open /path/to/does/not/exist using mode r
     */
    public function testThrowsExceptionNotWarning()
    {
        Util::tryFopen('/path/to/does/not/exist', 'r');
    }

    public function testKeepsPositionOfResource()
    {
        $h = fopen(__FILE__, 'r');
        fseek($h, 10);
        $stream = Util::getStream($h);
        $this->assertEquals(10, $stream->tell());
        $stream->close();
    }

    public function testCreatesWithFactory()
    {
        $stream = Util::getStream('foo');
        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertEquals('foo', $stream->getContents());
        $stream->close();
    }

    public function testFactoryCreatesFromEmptyString()
    {
        $s = Util::getStream();
        $this->assertInstanceOf(Stream::class, $s);
    }

    public function testFactoryCreatesFromNull()
    {
        $s = Util::getStream(null);
        $this->assertInstanceOf(Stream::class, $s);
    }

    public function testFactoryCreatesFromResource()
    {
        $r = fopen(__FILE__, 'r');
        $s = Util::getStream($r);
        $this->assertInstanceOf(Stream::class, $s);
        $this->assertSame(file_get_contents(__FILE__), (string) $s);
    }

    public function testFactoryCreatesFromObjectWithToString()
    {
        $r = new HasToString();
        $s = Util::getStream($r);
        $this->assertInstanceOf(Stream::class, $s);
        $this->assertEquals('foo', (string) $s);
    }

    public function testCreatePassesThrough()
    {
        $s = Util::getStream('foo');
        $this->assertSame($s, Util::getStream($s));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionForUnknown()
    {
        Util::getStream(new \stdClass());
    }

    public function testReturnsCustomMetadata()
    {
        $s = Util::getStream('foo', ['metadata' => ['hwm' => 3]]);

        $this->assertEquals(3, $s->getMetadata('hwm'));
        $this->assertArrayHasKey('hwm', $s->getMetadata());
    }

    public function testCanSetSize()
    {
        $s = Util::getStream('', ['size' => 10]);

        $this->assertEquals(10, $s->getSize());
    }

    public function testCanCreateIteratorBasedStream()
    {
        $a = new ArrayIterator(['foo', 'bar', '123']);
        $p = Util::getStream($a);

        $this->assertInstanceOf(PumpStream::class, $p);
        $this->assertEquals('foo', $p->read(3));
        $this->assertFalse($p->eof());
        $this->assertEquals('b', $p->read(1));
        $this->assertEquals('a', $p->read(1));
        $this->assertEquals('r12', $p->read(3));
        $this->assertFalse($p->eof());
        $this->assertEquals('3', $p->getContents());
        $this->assertTrue($p->eof());
        $this->assertEquals(9, $p->tell());
    }

    public function dataNormalizeFiles()
    {
        return [
            'Single file' => [
                [
                    'file' => [
                        'name' => 'MyFile.txt',
                        'type' => 'text/plain',
                        'tmp_name' => '/tmp/php/php1h4j1o',
                        'error' => '0',
                        'size' => '123',
                    ],
                ],
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
            ],
            'Empty file' => [
                [
                    'image_file' => [
                        'name' => '',
                        'type' => '',
                        'tmp_name' => '',
                        'error' => '4',
                        'size' => '0',
                    ],
                ],
                [
                    'image_file' => new UploadedFile(
                        '',
                        0,
                        UPLOAD_ERR_NO_FILE,
                        '',
                        ''
                    ),
                ],
            ],
            'Already Converted' => [
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
            ],
            'Already Converted array' => [
                [
                    'file' => [
                        new UploadedFile(
                            '/tmp/php/php1h4j1o',
                            123,
                            UPLOAD_ERR_OK,
                            'MyFile.txt',
                            'text/plain'
                        ),
                        new UploadedFile(
                            '',
                            0,
                            UPLOAD_ERR_NO_FILE,
                            '',
                            ''
                        ),
                    ],
                ],
                [
                    'file' => [
                        new UploadedFile(
                            '/tmp/php/php1h4j1o',
                            123,
                            UPLOAD_ERR_OK,
                            'MyFile.txt',
                            'text/plain'
                        ),
                        new UploadedFile(
                            '',
                            0,
                            UPLOAD_ERR_NO_FILE,
                            '',
                            ''
                        ),
                    ],
                ],
            ],
            'Multiple files' => [
                [
                    'text_file' => [
                        'name' => 'MyFile.txt',
                        'type' => 'text/plain',
                        'tmp_name' => '/tmp/php/php1h4j1o',
                        'error' => '0',
                        'size' => '123',
                    ],
                    'image_file' => [
                        'name' => '',
                        'type' => '',
                        'tmp_name' => '',
                        'error' => '4',
                        'size' => '0',
                    ],
                ],
                [
                    'text_file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                    'image_file' => new UploadedFile(
                        '',
                        0,
                        UPLOAD_ERR_NO_FILE,
                        '',
                        ''
                    ),
                ],
            ],
            'Nested files' => [
                [
                    'file' => [
                        'name' => [
                            0 => 'MyFile.txt',
                            1 => 'Image.png',
                        ],
                        'type' => [
                            0 => 'text/plain',
                            1 => 'image/png',
                        ],
                        'tmp_name' => [
                            0 => '/tmp/php/hp9hskjhf',
                            1 => '/tmp/php/php1h4j1o',
                        ],
                        'error' => [
                            0 => '0',
                            1 => '0',
                        ],
                        'size' => [
                            0 => '123',
                            1 => '7349',
                        ],
                    ],
                    'nested' => [
                        'name' => [
                            'other' => 'Flag.txt',
                            'test' => [
                                0 => 'Stuff.txt',
                                1 => '',
                            ],
                        ],
                        'type' => [
                            'other' => 'text/plain',
                            'test' => [
                                0 => 'text/plain',
                                1 => '',
                            ],
                        ],
                        'tmp_name' => [
                            'other' => '/tmp/php/hp9hskjhf',
                            'test' => [
                                0 => '/tmp/php/asifu2gp3',
                                1 => '',
                            ],
                        ],
                        'error' => [
                            'other' => '0',
                            'test' => [
                                0 => '0',
                                1 => '4',
                            ],
                        ],
                        'size' => [
                            'other' => '421',
                            'test' => [
                                0 => '32',
                                1 => '0',
                            ],
                        ],
                    ],
                ],
                [
                    'file' => [
                        0 => new UploadedFile(
                            '/tmp/php/hp9hskjhf',
                            123,
                            UPLOAD_ERR_OK,
                            'MyFile.txt',
                            'text/plain'
                        ),
                        1 => new UploadedFile(
                            '/tmp/php/php1h4j1o',
                            7349,
                            UPLOAD_ERR_OK,
                            'Image.png',
                            'image/png'
                        ),
                    ],
                    'nested' => [
                        'other' => new UploadedFile(
                            '/tmp/php/hp9hskjhf',
                            421,
                            UPLOAD_ERR_OK,
                            'Flag.txt',
                            'text/plain'
                        ),
                        'test' => [
                            0 => new UploadedFile(
                                '/tmp/php/asifu2gp3',
                                32,
                                UPLOAD_ERR_OK,
                                'Stuff.txt',
                                'text/plain'
                            ),
                            1 => new UploadedFile(
                                '',
                                0,
                                UPLOAD_ERR_NO_FILE,
                                '',
                                ''
                            ),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataNormalizeFiles
     */
    public function testNormalizeFiles($files, $expected)
    {
        $result = Util::normalizeFiles($files);
        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid value in files specification
     */
    public function testNormalizeFilesRaisesException()
    {
        Util::normalizeFiles(['test' => 'something']);
    }
}
