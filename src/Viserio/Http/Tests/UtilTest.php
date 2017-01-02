<?php
declare(strict_types=1);
namespace Viserio\Http\Tests;

use Viserio\Http\Stream;
use Viserio\Http\Stream\FnStream;
use Viserio\Http\UploadedFile;
use Viserio\Http\Util;
use PHPUnit\Framework\TestCase;

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
     * @expectedExceptionMessage Unable to open /path/to/does/not/exist using mode r
     */
    public function testThrowsExceptionNotWarning()
    {
        Util::tryFopen('/path/to/does/not/exist', 'r');
    }

    public function dataNormalizeFiles()
    {
        return [
            'Single file' => [
                [
                    'file' => [
                        'name'     => 'MyFile.txt',
                        'type'     => 'text/plain',
                        'tmp_name' => '/tmp/php/php1h4j1o',
                        'error'    => '0',
                        'size'     => '123',
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
                        'name'     => '',
                        'type'     => '',
                        'tmp_name' => '',
                        'error'    => '4',
                        'size'     => '0',
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
                        'name'     => 'MyFile.txt',
                        'type'     => 'text/plain',
                        'tmp_name' => '/tmp/php/php1h4j1o',
                        'error'    => '0',
                        'size'     => '123',
                    ],
                    'image_file' => [
                        'name'     => '',
                        'type'     => '',
                        'tmp_name' => '',
                        'error'    => '4',
                        'size'     => '0',
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
                            'test'  => [
                                0 => 'Stuff.txt',
                                1 => '',
                            ],
                        ],
                        'type' => [
                            'other' => 'text/plain',
                            'test'  => [
                                0 => 'text/plain',
                                1 => '',
                            ],
                        ],
                        'tmp_name' => [
                            'other' => '/tmp/php/hp9hskjhf',
                            'test'  => [
                                0 => '/tmp/php/asifu2gp3',
                                1 => '',
                            ],
                        ],
                        'error' => [
                            'other' => '0',
                            'test'  => [
                                0 => '0',
                                1 => '4',
                            ],
                        ],
                        'size' => [
                            'other' => '421',
                            'test'  => [
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
     *
     * @param mixed $files
     * @param mixed $expected
     */
    public function testNormalizeFiles($files, $expected)
    {
        $result = Util::normalizeFiles($files);
        self::assertEquals($expected, $result);
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
