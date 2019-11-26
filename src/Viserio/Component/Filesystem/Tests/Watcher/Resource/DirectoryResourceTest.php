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

namespace Viserio\Component\Filesystem\Tests\Watcher\Resource;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent;
use Viserio\Component\Filesystem\Watcher\Resource\DirectoryResource;

/**
 * @internal
 *
 * @small
 */
final class DirectoryResourceTest extends TestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $root;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testCreateFile(): void
    {
        $this->root->addChild(new vfsStreamDirectory('testCreateFile'));

        $dir = $this->root->getChild('testCreateFile')->url();

        $resource = new DirectoryResource($dir);

        self::assertSame([], $resource->detectChanges());

        \touch($dir . '/foo.txt');

        self::assertEquals([new FileChangeEvent($dir . \DIRECTORY_SEPARATOR . 'foo.txt', FileChangeEvent::FILE_CREATED)], $resource->detectChanges());
        self::assertSame([], $resource->detectChanges());
    }

    public function testDeleteFile(): void
    {
        $this->root->addChild(new vfsStreamDirectory('testDeleteFile'));

        $dir = $this->root->getChild('testDeleteFile')->url();

        \touch($dir . '/foo.txt');
        \touch($dir . '/bar.txt');

        $resource = new DirectoryResource($dir);

        self::assertSame([], $resource->detectChanges());

        \unlink($dir . '/foo.txt');

        self::assertEquals([new FileChangeEvent($dir . \DIRECTORY_SEPARATOR . 'foo.txt', FileChangeEvent::FILE_DELETED)], $resource->detectChanges());
        self::assertSame([], $resource->detectChanges());
    }

    public function testFileChanges(): void
    {
        $this->root->addChild(new vfsStreamDirectory('testFileChanges'));

        $dir = $this->root->getChild('testFileChanges')->url();

        \touch($dir . '/foo.txt');
        \touch($dir . '/bar.txt');

        $resource = new DirectoryResource($dir);

        self::assertSame([], $resource->detectChanges());

        \touch($dir . '/foo.txt', \time() + 1);

        self::assertEquals([new FileChangeEvent($dir . \DIRECTORY_SEPARATOR . 'foo.txt', FileChangeEvent::FILE_CHANGED)], $resource->detectChanges());
        self::assertSame([], $resource->detectChanges());
    }
}
