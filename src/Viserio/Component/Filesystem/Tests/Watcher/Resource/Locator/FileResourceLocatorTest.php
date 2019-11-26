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

namespace Viserio\Component\Filesystem\Tests\Watcher\Resource\Locator;

use ArrayIterator;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Viserio\Component\Filesystem\Watcher\Resource\ArrayResource;
use Viserio\Component\Filesystem\Watcher\Resource\DirectoryResource;
use Viserio\Component\Filesystem\Watcher\Resource\FileResource;
use Viserio\Component\Filesystem\Watcher\Resource\Locator\FileResourceLocator;

/**
 * @internal
 *
 * @small
 */
final class FileResourceLocatorTest extends TestCase
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

//    public function testLocateIterator(): void
//    {
//        $path = new ArrayIterator([$this->createFile('foo.txt')]);
//
//        self::assertEquals(new ArrayResource([new FileResource($this->root->url() . \DIRECTORY_SEPARATOR . 'foo.txt')]), FileResourceLocator::locate($path));
//    }
//
//    public function testLocateSplFileInfo(): void
//    {
//        $path = new SplFileInfo($this->createFile('foo.txt'));
//
//        self::assertEquals(new FileResource($this->root->url() . \DIRECTORY_SEPARATOR . 'foo.txt'), FileResourceLocator::locate($path));
//    }

    public function testFilePath(): void
    {
        $path = $this->createFile('foo.txt');

        self::assertEquals(new FileResource($this->root->url() . \DIRECTORY_SEPARATOR . 'foo.txt'), FileResourceLocator::locate($path));
    }

    public function testGlob(): void
    {
        $this->createFile('bar.txt');
        $this->createFile('foo.txt');

        self::assertEquals(
            new ArrayResource([new FileResource($this->root->url() . \DIRECTORY_SEPARATOR . 'bar.txt'), new FileResource($this->root->url() . \DIRECTORY_SEPARATOR . 'foo.txt')]),
            FileResourceLocator::locate($this->root->url() . \DIRECTORY_SEPARATOR . '*.txt')
        );
    }

    public function testArray(): void
    {
        $path = [$this->createFile('foo.txt')];

        self::assertEquals(new ArrayResource([new FileResource($this->root->url() . \DIRECTORY_SEPARATOR . 'foo.txt')]), FileResourceLocator::locate($path));
    }

    public function testDirectory(): void
    {
        $dir = $this->createDirectory('foobar');

        self::assertEquals(new DirectoryResource($this->root->url() . \DIRECTORY_SEPARATOR . 'foobar'), FileResourceLocator::locate($dir));
    }

    private function createFile(string $file): string
    {
        $file = vfsStream::newFile($file)
            ->at($this->root);

        return $file->url();
    }

    private function createDirectory(string $dir): string
    {
        $this->root->addChild(new vfsStreamDirectory($dir));

        return $this->root->getChild($dir)->url();
    }
}
