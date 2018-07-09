<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;
use Viserio\Component\Http\UploadedFile;
use Viserio\Component\Http\Util;

/**
 * @internal
 */
final class UtilTest extends TestCase
{
    public function testCopiesToString(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s = new Stream($stream);

        static::assertEquals('foobaz', Util::copyToString($s));

        $s->seek(0);

        static::assertEquals('foo', Util::copyToString($s, 3));
        static::assertEquals('baz', Util::copyToString($s, 3));
        static::assertEquals('', Util::copyToString($s));
    }

    public function testCopiesToStringStopsWhenReadFails(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s1 = FnStream::decorate($s1, [
            'read' => function () {
                return '';
            },
        ]);
        $result = Util::copyToString($s1);

        \fclose($stream);

        static::assertEquals('', $result);
    }

    public function testCopiesToStream(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s2 = new Stream(\fopen('php://temp', 'rb+'));

        Util::copyToStream($s1, $s2);

        static::assertEquals('foobaz', (string) $s2);

        $s2 = new Stream(\fopen('php://temp', 'rb+'));
        $s1->seek(0);

        Util::copyToStream($s1, $s2, 3);

        static::assertEquals('foo', (string) $s2);

        Util::copyToStream($s1, $s2, 3);

        static::assertEquals('foobaz', (string) $s2);
    }

    public function testCopyToStreamReadsInChunksInsteadOfAllInMemory(): void
    {
        $sizes = [];

        $s1 = new FnStream([
            'eof' => function () {
                return false;
            },
            'read' => function ($size) use (&$sizes) {
                $sizes[] = $size;

                return \str_repeat('.', $size);
            },
        ]);

        $s2 = new Stream(\fopen('php://temp', 'rb+'));

        Util::copyToStream($s1, $s2, 16394);

        $s2->seek(0);

        static::assertEquals(16394, \mb_strlen($s2->getContents()));
        static::assertEquals(8192, $sizes[0]);
        static::assertEquals(8192, $sizes[1]);
        static::assertEquals(10, $sizes[2]);
    }

    public function testStopsCopyToStreamWhenWriteFails(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s2 = new Stream(\fopen('php://temp', 'rb+'));
        $s2 = FnStream::decorate($s2, ['write' => function () {
            return 0;
        }]);
        Util::copyToStream($s1, $s2);

        static::assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenWriteFailsWithMaxLen(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s2 = new Stream(\fopen('php://temp', 'rb+'));
        $s2 = FnStream::decorate($s2, ['write' => function () {
            return 0;
        }]);

        Util::copyToStream($s1, $s2, 10);

        static::assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenReadFailsWithMaxLen(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s1 = FnStream::decorate($s1, ['read' => function () {
            return '';
        }]);
        $s2 = new Stream(\fopen('php://temp', 'rb+'));

        Util::copyToStream($s1, $s2, 10);

        static::assertEquals('', (string) $s2);
    }

    public function testOpensFilesSuccessfully(): void
    {
        $r = Util::tryFopen(__FILE__, 'r');

        static::assertInternalType('resource', $r);

        \fclose($r);
    }

    public function testThrowsExceptionNotWarning(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to open [/path/to/does/not/exist] using mode r');

        Util::tryFopen('/path/to/does/not/exist', 'r');
    }

    /**
     * @return array
     */
    public function dataNormalizeFiles(): array
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
                        \UPLOAD_ERR_OK,
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
                        \UPLOAD_ERR_NO_FILE,
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
                        \UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        \UPLOAD_ERR_OK,
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
                            \UPLOAD_ERR_OK,
                            'MyFile.txt',
                            'text/plain'
                        ),
                        new UploadedFile(
                            '',
                            0,
                            \UPLOAD_ERR_NO_FILE,
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
                            \UPLOAD_ERR_OK,
                            'MyFile.txt',
                            'text/plain'
                        ),
                        new UploadedFile(
                            '',
                            0,
                            \UPLOAD_ERR_NO_FILE,
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
                        \UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                    'image_file' => new UploadedFile(
                        '',
                        0,
                        \UPLOAD_ERR_NO_FILE,
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
                            \UPLOAD_ERR_OK,
                            'MyFile.txt',
                            'text/plain'
                        ),
                        1 => new UploadedFile(
                            '/tmp/php/php1h4j1o',
                            7349,
                            \UPLOAD_ERR_OK,
                            'Image.png',
                            'image/png'
                        ),
                    ],
                    'nested' => [
                        'other' => new UploadedFile(
                            '/tmp/php/hp9hskjhf',
                            421,
                            \UPLOAD_ERR_OK,
                            'Flag.txt',
                            'text/plain'
                        ),
                        'test' => [
                            0 => new UploadedFile(
                                '/tmp/php/asifu2gp3',
                                32,
                                \UPLOAD_ERR_OK,
                                'Stuff.txt',
                                'text/plain'
                            ),
                            1 => new UploadedFile(
                                '',
                                0,
                                \UPLOAD_ERR_NO_FILE,
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
    public function testNormalizeFiles($files, $expected): void
    {
        static::assertEquals($expected, Util::normalizeFiles($files));
    }

    public function testNormalizeFilesRaisesException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value in files specification');

        Util::normalizeFiles(['test' => 'something']);
    }

    public function testFlatFile(): void
    {
        $files = [
            'avatar' => [
                'tmp_name' => 'phpUxcOty',
                'name'     => 'my-avatar.png',
                'size'     => 90996,
                'type'     => 'image/png',
                'error'    => 0,
            ],
        ];

        $normalised = Util::normalizeFiles($files);

        static::assertCount(1, $normalised);
        static::assertInstanceOf(UploadedFileInterface::class, $normalised['avatar']);
        static::assertEquals('my-avatar.png', $normalised['avatar']->getClientFilename());
    }

    public function testNestedFile(): void
    {
        $files = [
            'my-form' => [
                'details' => [
                    'avatar' => [
                        'tmp_name' => 'phpUxcOty',
                        'name'     => 'my-avatar.png',
                        'size'     => 90996,
                        'type'     => 'image/png',
                        'error'    => 0,
                    ],
                ],
            ],
        ];

        $normalised = Util::normalizeFiles($files);

        static::assertCount(1, $normalised);
        static::assertEquals('my-avatar.png', $normalised['my-form']['details']['avatar']->getClientFilename());
    }

    public function testNumericIndexedFiles(): void
    {
        $files = [
            'my-form' => [
                'details' => [
                    'avatars' => [
                        'tmp_name' => [
                            0 => 'abc123',
                            1 => 'duck123',
                            2 => 'goose123',
                        ],
                        'name' => [
                            0 => 'file1.txt',
                            1 => 'file2.txt',
                            2 => 'file3.txt',
                        ],
                        'size' => [
                            0 => 100,
                            1 => 240,
                            2 => 750,
                        ],
                        'type' => [
                            0 => 'plain/txt',
                            1 => 'image/jpg',
                            2 => 'image/png',
                        ],
                        'error' => [
                            0 => 0,
                            1 => 0,
                            2 => 0,
                        ],
                    ],
                ],
            ],
        ];

        $normalised = Util::normalizeFiles($files);

        static::assertCount(3, $normalised['my-form']['details']['avatars']);
        static::assertEquals('file1.txt', $normalised['my-form']['details']['avatars'][0]->getClientFilename());
        static::assertEquals('file2.txt', $normalised['my-form']['details']['avatars'][1]->getClientFilename());
        static::assertEquals('file3.txt', $normalised['my-form']['details']['avatars'][2]->getClientFilename());
    }

    /**
     * This case covers upfront numeric index which moves the tmp_name/size/etc fields further up the array tree.
     */
    public function testNumericFirstIndexedFiles(): void
    {
        $files = [
            'slide-shows' => [
                'tmp_name' => [
                    // Note: Nesting *under* tmp_name/etc
                    0 => [
                        'slides' => [
                            0 => '/tmp/phpYzdqkD',
                            1 => '/tmp/phpYzdfgh',
                        ],
                    ],
                ],
                'error' => [
                    0 => [
                        'slides' => [
                            0 => 0,
                            1 => 0,
                        ],
                    ],
                ],
                'name' => [
                    0 => [
                        'slides' => [
                            0 => 'foo.txt',
                            1 => 'bar.txt',
                        ],
                    ],
                ],
                'size' => [
                    0 => [
                        'slides' => [
                            0 => 123,
                            1 => 200,
                        ],
                    ],
                ],
                'type' => [
                    0 => [
                        'slides' => [
                            0 => 'text/plain',
                            1 => 'text/plain',
                        ],
                    ],
                ],
            ],
        ];

        $normalised = Util::normalizeFiles($files);

        static::assertCount(2, $normalised['slide-shows'][0]['slides']);
        static::assertEquals('foo.txt', $normalised['slide-shows'][0]['slides'][0]->getClientFilename());
        static::assertEquals('bar.txt', $normalised['slide-shows'][0]['slides'][1]->getClientFilename());
    }
}
