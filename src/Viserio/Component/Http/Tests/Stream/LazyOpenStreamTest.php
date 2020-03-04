<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Http\Tests\Stream;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Stream\LazyOpenStream;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class LazyOpenStreamTest extends TestCase
{
    /** @var string */
    private $fname;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        \mkdir(__DIR__ . \DIRECTORY_SEPARATOR . 'tmp');

        $this->fname = (string) \tempnam(__DIR__ . \DIRECTORY_SEPARATOR . 'tmp', 'tfile');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if (\file_exists($this->fname)) {
            \unlink($this->fname);
        }

        \rmdir(__DIR__ . \DIRECTORY_SEPARATOR . 'tmp');
    }

    public function testOpensLazily(): void
    {
        $lazy = new LazyOpenStream($this->fname, 'w+');
        $lazy->write('foo');

        self::assertIsArray($lazy->getMetadata());
        self::assertFileExists($this->fname);
        self::assertStringEqualsFile($this->fname, 'foo');
        self::assertEquals('foo', (string) $lazy);
    }

    public function testProxiesToFile(): void
    {
        \file_put_contents($this->fname, 'foo');
        $lazy = new LazyOpenStream($this->fname, 'r');

        self::assertEquals('foo', $lazy->read(4));
        self::assertTrue($lazy->eof());
        self::assertEquals(3, $lazy->tell());
        self::assertTrue($lazy->isReadable());
        self::assertTrue($lazy->isSeekable());
        self::assertFalse($lazy->isWritable());

        $lazy->seek(1);

        self::assertEquals('oo', $lazy->getContents());
        self::assertEquals('foo', (string) $lazy);
        self::assertEquals(3, $lazy->getSize());
        self::assertIsArray($lazy->getMetadata());

        $lazy->close();
    }

    public function testDetachesUnderlyingStream(): void
    {
        \file_put_contents($this->fname, 'foo');

        $lazy = new LazyOpenStream($this->fname, 'r');

        $r = $lazy->detach();

        self::assertIsResource($r);
        \fseek($r, 0);

        self::assertEquals('foo', \stream_get_contents($r));

        \fclose($r);
    }
}
