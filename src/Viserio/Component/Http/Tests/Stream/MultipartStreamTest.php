<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Http\Stream\FnStream;
use Viserio\Component\Http\Stream\MultipartStream;
use Viserio\Component\Http\Util;

/**
 * @internal
 */
final class MultipartStreamTest extends TestCase
{
    public function testCreatesDefaultBoundary(): void
    {
        $b = new MultipartStream();

        $this->assertNotEmpty($b->getBoundary());
    }

    public function testCanProvideBoundary(): void
    {
        $b = new MultipartStream([], 'foo');
        $this->assertEquals('foo', $b->getBoundary());
    }

    public function testIsNotWritable(): void
    {
        $b = new MultipartStream();
        $this->assertFalse($b->isWritable());
    }

    public function testCanCreateEmptyStream(): void
    {
        $b        = new MultipartStream();
        $boundary = $b->getBoundary();
        $this->assertSame("--{$boundary}--\r\n", $b->getContents());
        $this->assertSame(\strlen($boundary) + 6, $b->getSize());
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
                'name'     => 'foo',
                'contents' => 'bar',
            ],
            [
                'name'     => 'baz',
                'contents' => 'bam',
            ],
        ], 'boundary');

        $this->assertEquals(
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
                'name'     => 'int',
                'contents' => 1,
            ],
            [
                'name'     => 'bool',
                'contents' => false,
            ],
            [
                'name'     => 'bool2',
                'contents' => true,
            ],
            [
                'name'     => 'float',
                'contents' => 1.1,
            ],
        ], 'boundary');

        $this->assertEquals(
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
            'getMetadata' => function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar.txt';
            },
        ]);

        $f2 = FnStream::decorate(Util::createStreamFor('baz'), [
            'getMetadata' => function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'baz.jpeg';
            },
        ]);

        $f3 = FnStream::decorate(Util::createStreamFor('bar'), [
            'getMetadata' => function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar.gif';
            },
        ]);

        $b = new MultipartStream([
            [
                'name'     => 'foo',
                'contents' => $f1,
            ],
            [
                'name'     => 'qux',
                'contents' => $f2,
            ],
            [
                'name'     => 'qux',
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

        $this->assertEquals($expected, \str_replace("\r", '', $b));
    }

    public function testSerializesFilesWithCustomHeaders(): void
    {
        $fixtureDir = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture';

        $f1 = FnStream::decorate(Util::createStreamFor('foo'), [
            'getMetadata' => function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar.txt';
            },
        ]);

        $b = new MultipartStream([
            [
                'name'     => 'foo',
                'contents' => $f1,
                'headers'  => [
                    'x-foo'               => 'bar',
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

        $this->assertEquals($expected, \str_replace("\r", '', $b));
    }

    public function testSerializesFilesWithCustomHeadersAndMultipleValues(): void
    {
        $fixtureDir = \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture';

        $f1 = FnStream::decorate(Util::createStreamFor('foo'), [
            'getMetadata' => function () use ($fixtureDir) {
                return $fixtureDir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar.txt';
            },
        ]);

        $f2 = FnStream::decorate(Util::createStreamFor('baz'), [
            'getMetadata' => function () {
                return '/foo/baz.jpg';
            },
        ]);

        $b = new MultipartStream([
            [
                'name'     => 'foo',
                'contents' => $f1,
                'headers'  => [
                    'x-foo'               => 'bar',
                    'content-disposition' => 'custom',
                ],
            ],
            [
                'name'     => 'foo',
                'contents' => $f2,
                'headers'  => ['cOntenT-Type' => 'custom'],
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

        $this->assertEquals($expected, \str_replace("\r", '', $b));
    }
}
