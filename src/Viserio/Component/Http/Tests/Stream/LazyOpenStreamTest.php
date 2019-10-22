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
use Viserio\Component\Http\Stream\LazyOpenStream;

/**
 * @internal
 *
 * @small
 */
final class LazyOpenStreamTest extends TestCase
{
    private $fname;

    protected function setup(): void
    {
        \mkdir(__DIR__ . \DIRECTORY_SEPARATOR . 'tmp');

        $this->fname = \tempnam(__DIR__ . \DIRECTORY_SEPARATOR . 'tmp', 'tfile');
    }

    protected function tearDown(): void
    {
        if (\file_exists($this->fname)) {
            \unlink($this->fname);
        }

        \rmdir(__DIR__ . \DIRECTORY_SEPARATOR . 'tmp');

        parent::tearDown();
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
