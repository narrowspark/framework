<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Http\Exception\RuntimeException;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;
use Viserio\Component\Http\Tests\Fixture\HasToString;
use Viserio\Component\Http\UploadedFile;
use Viserio\Component\Http\Util;

/**
 * @internal
 */
final class UtilTest extends TestCase
{
    public function testMarshalsExpectedHeadersFromServerArray(): void
    {
        $server = [
            'HTTP_COOKIE'        => 'COOKIE',
            'HTTP_AUTHORIZATION' => 'token',
            'HTTP_CONTENT_TYPE'  => 'application/json',
            'HTTP_ACCEPT'        => 'application/json',
            'HTTP_X_FOO_BAR'     => 'FOOBAR',
            'CONTENT_MD5'        => 'CONTENT-MD5',
            'CONTENT_LENGTH'     => 'UNSPECIFIED',
        ];
        $expected = [
            'Cookie'         => 'COOKIE',
            'Authorization'  => 'token',
            'Content-Type'   => 'application/json',
            'Accept'         => 'application/json',
            'X-Foo-Bar'      => 'FOOBAR',
            'Content-Md5'    => 'CONTENT-MD5',
            'Content-Length' => 'UNSPECIFIED',
        ];

        $this->assertSame($expected, Util::getAllHeaders($server));
    }

    public function testMarshalInvalidHeadersStrippedFromServerArray(): void
    {
        $server = [
            'COOKIE'             => 'COOKIE',
            'HTTP_AUTHORIZATION' => 'token',
            'MD5'                => 'CONTENT-MD5',
            'CONTENT_LENGTH'     => 'UNSPECIFIED',
        ];
        //Headers that don't begin with HTTP_ or CONTENT_ will not be returned
        $expected = [
            'Authorization'  => 'token',
            'Content-Length' => 'UNSPECIFIED',
        ];

        $this->assertSame($expected, Util::getAllHeaders($server));
    }

    public function testMarshalsVariablesPrefixedByApacheFromServerArray(): void
    {
        // Non-prefixed versions will be preferred
        $server = [
            'HTTP_X_FOO_BAR'              => 'nonprefixed',
            'REDIRECT_HTTP_AUTHORIZATION' => 'token',
            'REDIRECT_HTTP_X_FOO_BAR'     => 'prefixed',
        ];
        $expected = [
            'Authorization' => 'token',
            'X-Foo-Bar'     => 'nonprefixed',
        ];

        $this->assertEquals($expected, Util::getAllHeaders($server));
    }

    /**
     * @dataProvider dataWorks
     *
     * @param string $testType
     * @param array  $expected
     * @param array  $server
     */
    public function testGetAllHeaders(string $testType, array $expected, array $server): void
    {
        foreach ($server as $key => $val) {
            $_SERVER[$key] = $val;
        }

        $this->assertEquals($expected, Util::getAllHeaders($_SERVER), "Error testing ${testType} works.");

        // Clean up.
        foreach ($server as $key => $val) {
            unset($_SERVER[$key]);
        }
    }

    /**
     * @return array
     */
    public function dataWorks(): array
    {
        return [
            [
                'normal case',
                [
                    'Key-One'                 => 'foo',
                    'Key-Two'                 => 'bar',
                    'Another-Key-For-Testing' => 'baz',
                ],
                [
                    'HTTP_KEY_ONE'                 => 'foo',
                    'HTTP_KEY_TWO'                 => 'bar',
                    'HTTP_ANOTHER_KEY_FOR_TESTING' => 'baz',
                ],
            ],
            [
                'Content-Type',
                [
                    'Content-Type' => 'two',
                ],
                [
                    'HTTP_CONTENT_TYPE' => 'one',
                    'CONTENT_TYPE'      => 'two',
                ],
            ],
            [
                'Content-Length',
                [
                    'Content-Length' => '222',
                ],
                [
                    'CONTENT_LENGTH'      => '222',
                    'HTTP_CONTENT_LENGTH' => '111',
                ],
            ],
            [
                'Content-Length (HTTP_CONTENT_LENGTH only)',
                [
                    'Content-Length' => '111',
                ],
                [
                    'HTTP_CONTENT_LENGTH' => '111',
                ],
            ],
            [
                'Content-MD5',
                [
                    'Content-Md5' => 'aef123',
                ],
                [
                    'CONTENT_MD5'      => 'aef123',
                    'HTTP_CONTENT_MD5' => 'fea321',
                ],
            ],
            [
                'Content-MD5 (HTTP_CONTENT_MD5 only)',
                [
                    'Content-Md5' => 'f123',
                ],
                [
                    'HTTP_CONTENT_MD5' => 'f123',
                ],
            ],
            [
                'Authorization (normal)',
                [
                    'Authorization' => 'testing',
                ],
                [
                    'HTTP_AUTHORIZATION' => 'testing',
                ],
            ],
            [
                'Authorization (redirect)',
                [
                    'Authorization' => 'testing redirect',
                ],
                [
                    'REDIRECT_HTTP_AUTHORIZATION' => 'testing redirect',
                ],
            ],
            [
                'Authorization (PHP_AUTH_USER + PHP_AUTH_PW)',
                [
                    'Authorization' => 'Basic ' . \base64_encode('foo:bar'),
                ],
                [
                    'PHP_AUTH_USER' => 'foo',
                    'PHP_AUTH_PW'   => 'bar',
                ],
            ],
            [
                'Authorization (PHP_AUTH_DIGEST)',
                [
                    'Authorization' => 'example-digest',
                ],
                [
                    'PHP_AUTH_DIGEST' => 'example-digest',
                ],
            ],
            [
                'Preserve keys when created with a zero value',
                [
                    'Accept'         => '0',
                    'Content-Length' => '0',
                ],
                [
                    'HTTP_ACCEPT'    => '0',
                    'CONTENT_LENGTH' => '0',
                ],
            ],
        ];
    }

    public function testCopiesToString(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s = new Stream($stream);

        $this->assertEquals('foobaz', Util::copyToString($s));

        $s->seek(0);

        $this->assertEquals('foo', Util::copyToString($s, 3));
        $this->assertEquals('baz', Util::copyToString($s, 3));
        $this->assertEquals('', Util::copyToString($s));
    }

    public function testCopiesToStringStopsWhenReadFails(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s1 = FnStream::decorate($s1, [
            'read' => static function () {
                return '';
            },
        ]);
        $result = Util::copyToString($s1);

        \fclose($stream);

        $this->assertEquals('', $result);
    }

    public function testCopiesToStream(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s2 = new Stream(\fopen('php://temp', 'r+b'));

        Util::copyToStream($s1, $s2);

        $this->assertEquals('foobaz', (string) $s2);

        $s2 = new Stream(\fopen('php://temp', 'r+b'));
        $s1->seek(0);

        Util::copyToStream($s1, $s2, 3);

        $this->assertEquals('foo', (string) $s2);

        Util::copyToStream($s1, $s2, 3);

        $this->assertEquals('foobaz', (string) $s2);
    }

    public function testCopyToStreamReadsInChunksInsteadOfAllInMemory(): void
    {
        $sizes = [];

        $s1 = new FnStream([
            'eof' => static function () {
                return false;
            },
            'read' => static function ($size) use (&$sizes) {
                $sizes[] = $size;

                return \str_repeat('.', $size);
            },
        ]);

        $s2 = new Stream(\fopen('php://temp', 'r+b'));

        Util::copyToStream($s1, $s2, 16394);

        $s2->seek(0);

        $this->assertEquals(16394, \strlen($s2->getContents()));
        $this->assertEquals(8192, $sizes[0]);
        $this->assertEquals(8192, $sizes[1]);
        $this->assertEquals(10, $sizes[2]);
    }

    public function testStopsCopyToStreamWhenWriteFails(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s2 = new Stream(\fopen('php://temp', 'r+b'));
        $s2 = FnStream::decorate($s2, ['write' => static function () {
            return 0;
        }]);
        Util::copyToStream($s1, $s2);

        $this->assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenWriteFailsWithMaxLen(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s2 = new Stream(\fopen('php://temp', 'r+b'));
        $s2 = FnStream::decorate($s2, ['write' => static function () {
            return 0;
        }]);

        Util::copyToStream($s1, $s2, 10);

        $this->assertEquals('', (string) $s2);
    }

    public function testStopsCopyToSteamWhenReadFailsWithMaxLen(): void
    {
        $body   = 'foobaz';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $s1 = new Stream($stream);
        $s1 = FnStream::decorate($s1, ['read' => static function () {
            return '';
        }]);
        $s2 = new Stream(\fopen('php://temp', 'r+b'));

        Util::copyToStream($s1, $s2, 10);

        $this->assertEquals('', (string) $s2);
    }

    public function testOpensFilesSuccessfully(): void
    {
        $r = Util::tryFopen(__FILE__, 'r');

        $this->assertIsResource($r);

        \fclose($r);
    }

    public function testThrowsExceptionNotWarning(): void
    {
        $this->expectException(RuntimeException::class);
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
        $this->assertEquals($expected, Util::normalizeFiles($files));
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

        $this->assertCount(1, $normalised);
        $this->assertInstanceOf(UploadedFileInterface::class, $normalised['avatar']);
        $this->assertEquals('my-avatar.png', $normalised['avatar']->getClientFilename());
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

        $this->assertCount(1, $normalised);
        $this->assertEquals('my-avatar.png', $normalised['my-form']['details']['avatar']->getClientFilename());
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

        $this->assertCount(3, $normalised['my-form']['details']['avatars']);
        $this->assertEquals('file1.txt', $normalised['my-form']['details']['avatars'][0]->getClientFilename());
        $this->assertEquals('file2.txt', $normalised['my-form']['details']['avatars'][1]->getClientFilename());
        $this->assertEquals('file3.txt', $normalised['my-form']['details']['avatars'][2]->getClientFilename());
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

        $this->assertCount(2, $normalised['slide-shows'][0]['slides']);
        $this->assertEquals('foo.txt', $normalised['slide-shows'][0]['slides'][0]->getClientFilename());
        $this->assertEquals('bar.txt', $normalised['slide-shows'][0]['slides'][1]->getClientFilename());
    }

    public function testKeepsPositionOfResource(): void
    {
        $handler = \fopen(__FILE__, 'r');

        \fseek($handler, 10);

        $stream = Util::createStreamFor($handler);

        $this->assertEquals(10, $stream->tell());

        $stream->close();
    }

    public function testCreatesWithFactory(): void
    {
        $stream = Util::createStreamFor('foo');

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertEquals('foo', $stream->getContents());

        $stream->close();
    }

    public function testFactoryCreatesFromEmptyString(): void
    {
        $this->assertInstanceOf(Stream::class, Util::createStreamFor());
    }

    public function testFactoryCreatesFromNull(): void
    {
        $this->assertInstanceOf(Stream::class, Util::createStreamFor(null));
    }

    public function testFactoryCreatesFromResource(): void
    {
        $resource = \fopen(__FILE__, 'r');
        $stream   = Util::createStreamFor($resource);

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertSame(\file_get_contents(__FILE__), (string) $stream);
    }

    public function testFactoryCreatesFromObjectWithToString(): void
    {
        $resource = new HasToString();
        $stream   = Util::createStreamFor($resource);

        $this->assertInstanceOf(Stream::class, $stream);
        $this->assertEquals('foo', (string) $stream);
    }

    public function testCreatePassesThrough(): void
    {
        $stream = Util::createStreamFor('foo');

        $this->assertSame($stream, Util::createStreamFor($stream));
    }

    public function testThrowsExceptionForUnknown(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Util::createStreamFor(new \stdClass());
    }

    public function testReturnsCustomMetadata(): void
    {
        $stream = Util::createStreamFor('foo', ['metadata' => ['hwm' => 3]]);

        $this->assertEquals(3, $stream->getMetadata('hwm'));
        $this->assertArrayHasKey('hwm', $stream->getMetadata());
    }

    public function testCanSetSize(): void
    {
        $stream = Util::createStreamFor('', ['size' => 10]);

        $this->assertEquals(10, $stream->getSize());
    }

    public function testCanCreateIteratorBasedStream(): void
    {
        $stream = Util::createStreamFor(new ArrayIterator(['foo', 'bar', '123']));

        $this->assertInstanceOf(Stream\PumpStream::class, $stream);
        $this->assertEquals('foo', $stream->read(3));
        $this->assertFalse($stream->eof());
        $this->assertEquals('b', $stream->read(1));
        $this->assertEquals('a', $stream->read(1));
        $this->assertEquals('r12', $stream->read(3));
        $this->assertFalse($stream->eof());
        $this->assertEquals('3', $stream->getContents());
        $this->assertTrue($stream->eof());
        $this->assertEquals(9, $stream->tell());
    }

    public function testCanCreateCallbackBasedStream(): void
    {
        $stream = Util::createStreamFor(static function () {
            return Util::createStreamFor();
        });

        $this->assertInstanceOf(Stream\PumpStream::class, $stream);
    }

    public function testReadsLines(): void
    {
        $s = Util::createStreamFor("foo\nbaz\nbar");

        $this->assertEquals("foo\n", Util::readline($s));
        $this->assertEquals("baz\n", Util::readline($s));
        $this->assertEquals('bar', Util::readline($s));
    }

    public function testReadsLinesUpToMaxLength(): void
    {
        $s = Util::createStreamFor("12345\n");

        $this->assertEquals('123', Util::readline($s, 4));
        $this->assertEquals("45\n", Util::readline($s));
    }

    public function testReadLinesEof(): void
    {
        // Should return empty string on EOF
        $s = Util::createStreamFor("foo\nbar");

        while (! $s->eof()) {
            Util::readline($s);
        }

        $this->assertSame('', Util::readline($s));
    }
}
