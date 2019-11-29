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

    public function testLocateIterator(): void
    {
        $path = $this->createFile('foo.txt', 'testLocateIterator');

        self::assertEquals(new ArrayResource([new FileResource($path)]), FileResourceLocator::locate(new ArrayIterator([$path])));
    }

    public function testLocateSplFileInfo(): void
    {
        $path = $this->createFile('foo.txt', 'testLocateSplFileInfo');

        self::assertEquals(new FileResource($path), FileResourceLocator::locate(new SplFileInfo($path)));
    }

    public function testFilePath(): void
    {
        $path = $this->createFile('foo.txt', 'testFilePath');

        self::assertEquals(new FileResource($path), FileResourceLocator::locate($path));
    }

    public function testGlob(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('A "*" in filenames is not supported on Windows.');
        }

        $folderName = 'testGlob';

        $dirPath = $this->createDirectory($folderName);

        $this->createFile('bar.txt', $folderName, false);
        $this->createFile('foo.txt', $folderName, false);

        self::assertEquals(
            new ArrayResource([new FileResource($dirPath . \DIRECTORY_SEPARATOR . 'bar.txt'), new FileResource($dirPath . \DIRECTORY_SEPARATOR . 'foo.txt')]),
            FileResourceLocator::locate($dirPath . \DIRECTORY_SEPARATOR . '*.txt')
        );
    }

    public function testArray(): void
    {
        $path = $this->createFile('foo.txt', 'testArray');

        self::assertEquals(new ArrayResource([new FileResource($path)]), FileResourceLocator::locate([$path]));
    }

    public function testDirectory(): void
    {
        $dir = $this->createDirectory('foobar');

        self::assertEquals(new DirectoryResource($dir), FileResourceLocator::locate($dir));
    }

    private function createFile(string $file, string $folderName, bool $createFolder = true): string
    {
        $folderName = \strtolower($folderName);

        if ($createFolder) {
            $this->root->addChild(new vfsStreamDirectory($folderName, 0777));
        }

        $path = $this->root->getChild($folderName)->url() . \DIRECTORY_SEPARATOR . $file;

        \touch($path);

        return $path;
    }

    private function createDirectory(string $dir): string
    {
        $dir = \strtolower($dir);

        $this->root->addChild(new vfsStreamDirectory($dir, 0777));

        return $this->root->getChild($dir)->url();
    }
}
