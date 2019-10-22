<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream\FnStream;
use Viserio\Component\Http\Stream\MultipartStream;
use Viserio\Component\Http\Util;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class MultipartStreamTest extends TestCase
{
    public function testCreatesDefaultBoundary(): void
    {
        $b = new MultipartStream();

        self::assertNotEmpty($b->getBoundary());
    }

    public function testCanProvideBoundary(): void
    {
        $b = new MultipartStream([], 'foo');
        self::assertEquals('foo', $b->getBoundary());
    }

    public function testIsNotWritable(): void
    {
        $b = new MultipartStream();
        self::assertFalse($b->isWritable());
    }

    public function testCanCreateEmptyStream(): void
    {
        $b = new MultipartStream();
        $boundary = $b->getBoundary();
        self::assertSame("--{$boundary}--\r\n", $b->getContents());
        self::assertSame(\strlen($boundary) + 6, $b->getSize());
    }

    public function testValidatesFilesArrayElement(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MultipartStream([['foo' => 'bar']]);
    }

    public function testEnsuresFileHasName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MultipartStream([['contents' => 'bar']]);
    }

    public function testSerializesFields(): void
    {
        $b = new MultipartStream([
            [
                'name' => 'foo',
                'contents' => 'bar',
            ],
            [
                'name' => 'baz',
                'contents' => 'bam',
            ],
        ], 'boundary');

        self::assertEquals(
            "--boundary\r\nContent-Disposition: form-data; name=\"foo\"\r\nContent-Length: 3\r\n\r\n"
            . "bar\r\n--boundary\r\nContent-Disposition: form-data; name=\"baz\"\r\nContent-Length: 3"
            . "\r\n\r\nbam\r\n--boundary--\r\n",
            (string) $b
        );
    }

    public function testSerializesNonStringFields(): void
    {
        $b = new MultipartStream([
            [
                'name' => 'int',
                'contents' => 1,
            ],
            [
                'name' => 'bool',
                'contents' => false,
            ],
            [
                'name' => 'bool2',
                'contents' => true,
            ],
            [
                'name' => 'float',
                'contents' => 1.1,
            ],
        ], 'boundary');

        self::assertEquals(
            "--boundary\r\nContent-Disposition: form-data; name=\"int\"\r\nContent-Length: 1\r\n\r\n"
            . "1\r\n--boundary\r\nContent-Disposition: form-data; name=\"bool\"\r\n\r\n\r\n--boundary"
            . "\r\nContent-Disposition: form-data; name=\"bool2\"\r\nContent-Length: 1\r\n\r\n"
            . "1\r\n--boundary\r\nContent-Disposition: form-data; name=\"float\"\r\nContent-Length: 3"
            . "\r\n\r\n1.1\r\n--boundary--\r\n",
            (string) $b
        );
    }

    public function testSerializesFiles(): void
    {
        $fixtureDir = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture';

        $f1 = FnStream::decorate(Util::createStreamFor('foo'), [
            'getMetadata' => static function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar.txt';
            },
        ]);

        $f2 = FnStream::decorate(Util::createStreamFor('baz'), [
            'getMetadata' => static function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'baz.jpeg';
            },
        ]);

        $f3 = FnStream::decorate(Util::createStreamFor('bar'), [
            'getMetadata' => static function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar.gif';
            },
        ]);

        $b = new MultipartStream([
            [
                'name' => 'foo',
                'contents' => $f1,
            ],
            [
                'name' => 'qux',
                'contents' => $f2,
            ],
            [
                'name' => 'qux',
                'contents' => $f3,
            ],
        ], 'boundary');

        $expected = <<<'EOT'
--boundary
Content-Disposition: form-data; name="foo"; filename="bar.txt"
Content-Length: 3
Content-Type: text/plain

foo
--boundary
Content-Disposition: form-data; name="qux"; filename="baz.jpeg"
Content-Length: 3
Content-Type: image/jpeg

baz
--boundary
Content-Disposition: form-data; name="qux"; filename="bar.gif"
Content-Length: 3
Content-Type: image/gif

bar
--boundary--

EOT;

        self::assertEquals($expected, \str_replace("\r", '', $b));
    }

    public function testSerializesFilesWithCustomHeaders(): void
    {
        $fixtureDir = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture';

        $f1 = FnStream::decorate(Util::createStreamFor('foo'), [
            'getMetadata' => static function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar.txt';
            },
        ]);

        $b = new MultipartStream([
            [
                'name' => 'foo',
                'contents' => $f1,
                'headers' => [
                    'x-foo' => 'bar',
                    'content-disposition' => 'custom',
                ],
            ],
        ], 'boundary');

        $expected = <<<'EOT'
--boundary
x-foo: bar
content-disposition: custom
Content-Length: 3
Content-Type: text/plain

foo
--boundary--

EOT;

        self::assertEquals($expected, \str_replace("\r", '', $b));
    }

    public function testSerializesFilesWithCustomHeadersAndMultipleValues(): void
    {
        $fixtureDir = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture';

        $f1 = FnStream::decorate(Util::createStreamFor('foo'), [
            'getMetadata' => static function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar.txt';
            },
        ]);

        $f2 = FnStream::decorate(Util::createStreamFor('baz'), [
            'getMetadata' => static function () {
                return '/foo/baz.jpg';
            },
        ]);

        $b = new MultipartStream([
            [
                'name' => 'foo',
                'contents' => $f1,
                'headers' => [
                    'x-foo' => 'bar',
                    'content-disposition' => 'custom',
                ],
            ],
            [
                'name' => 'foo',
                'contents' => $f2,
                'headers' => ['cOntenT-Type' => 'custom'],
            ],
        ], 'boundary');

        $expected = <<<'EOT'
--boundary
x-foo: bar
content-disposition: custom
Content-Length: 3
Content-Type: text/plain

foo
--boundary
cOntenT-Type: custom
Content-Disposition: form-data; name="foo"; filename="baz.jpg"
Content-Length: 3

baz
--boundary--

EOT;

        self::assertEquals($expected, \str_replace("\r", '', $b));
    }
}
