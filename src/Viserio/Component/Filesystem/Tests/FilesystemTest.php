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

namespace Viserio\Component\Filesystem\Tests;

use InvalidArgumentException;
use League\Flysystem\Util;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Contract\Filesystem\Exception\FileNotFoundException;
use Viserio\Contract\Filesystem\Exception\IOException;

/**
 * @internal
 *
 * @small
 */
final class FilesystemTest extends TestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $root;

    /** @var \Viserio\Component\Filesystem\Filesystem */
    private $files;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
        $this->files = new Filesystem();
    }

    public function testReadRetrievesFiles(): void
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);

        self::assertEquals('Foo Bar', $this->files->read($file->url()));
    }

    public function testReadStreamToThrowException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->files->readStream('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'file.php');
    }

    public function testReadToThrowException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->files->read('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'file.php');
    }

    public function testUpdateStoresFiles(): void
    {
        $file = vfsStream::newFile('temp.txt')->at($this->root);

        $this->files->update($file->url(), 'Hello World');
        $this->files->update($file->url(), 'Hello World2');

        self::assertStringEqualsFile($file->url(), 'Hello World2');
    }

    public function testPutStoresFiles(): void
    {
        $file = vfsStream::newFile('temp.txt')->at($this->root);

        $this->files->put($file->url(), 'Hello World');

        self::assertStringEqualsFile($file->url(), 'Hello World');
    }

    public function testUpdateToThrowException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->files->update('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'file.php', 'Hello World');
    }

    public function testDeleteDirectory(): void
    {
        $this->root->addChild(new vfsStreamDirectory('temp'));

        $dir = $this->root->getChild('temp');
        $file = vfsStream::newFile('bar.txt')->withContent('bar')->at($dir);

        self::assertDirectoryExists($dir->url());
        self::assertFalse($this->files->deleteDirectory($file->url()));

        $this->files->deleteDirectory($dir->url());

        self::assertDirectoryNotExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'temp'));
        self::assertFileNotExists($file->url());
    }

    public function testCleanDirectory(): void
    {
        $this->root->addChild(new vfsStreamDirectory('tempdir'));

        $dir = $this->root->getChild('tempdir');
        $file = vfsStream::newFile('tempfoo.txt')->withContent('tempfoo')->at($dir);

        self::assertFalse($this->files->cleanDirectory($file->url()));
        $this->files->cleanDirectory($dir->url());

        self::assertDirectoryExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tempdir'));
        self::assertFileNotExists($file->url());
    }

    public function testDeleteRemovesFiles(): void
    {
        $file = vfsStream::newFile('unlucky.txt')->withContent('So sad')->at($this->root);

        self::assertTrue($this->files->has($file->url()));

        $this->files->delete([$file->url()]);

        self::assertFalse($this->files->has($file->url()));
    }

    public function testMoveMovesFiles(): void
    {
        $file = vfsStream::newFile('pop.txt')->withContent('pop')->at($this->root);
        $rock = $this->root->url() . \DIRECTORY_SEPARATOR . 'rock.txt';

        $this->files->move($file->url(), $rock);

        self::assertFileExists($rock);
        self::assertStringEqualsFile($rock, 'pop');
        self::assertFileNotExists($this->root->url() . \DIRECTORY_SEPARATOR . 'pop.txt');
    }

    public function testGetExtensionReturnsExtension(): void
    {
        $file = vfsStream::newFile('rock.csv')->withContent('pop,rock')->at($this->root);

        self::assertEquals('csv', $this->files->getExtension($file->url()));
    }

    public function testGetMimeTypeOutputsMimeType(): void
    {
        if (! \class_exists('Finfo')) {
            self::markTestSkipped('The PHP extension fileinfo is not installed.');
        }

        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        self::assertEquals('text/plain', $this->files->getMimetype($file->url()));
    }

    public function testGetSizeOutputsSize(): void
    {
        $content = LargeFileContent::withKilobytes(2);
        $file = vfsStream::newFile('2kb.txt')->withContent($content)->at($this->root);

        self::assertEquals($file->size(), $this->files->getSize($file->url()));
    }

    public function testIsDirectory(): void
    {
        $this->root->addChild(new vfsStreamDirectory('assets'));
        $dir = $this->root->getChild('assets');
        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        self::assertTrue($this->files->isDirectory($dir->url()));
        self::assertFalse($this->files->isDirectory($file->url()));
    }

    public function testAllFilesFindsFiles(): void
    {
        $this->root->addChild(new vfsStreamDirectory('languages'));

        $dir = $this->root->getChild('languages');
        $file1 = vfsStream::newFile('php.txt')->withContent('PHP')->at($dir);
        $file2 = vfsStream::newFile('c.txt')->withContent('C')->at($dir);

        $allFiles = $this->files->allFiles($dir->url());

        self::assertStringContainsString($file1->getName(), $allFiles[0]);
        self::assertStringContainsString($file2->getName(), $allFiles[1]);
    }

    public function testDirectoriesFindsDirectories(): void
    {
        $this->root->addChild(new vfsStreamDirectory('languages'));
        $this->root->addChild(new vfsStreamDirectory('music'));

        $directories = $this->files->directories($this->root->url());

        self::assertStringContainsString('vfs://root' . \DIRECTORY_SEPARATOR . 'languages', $directories[0]);
        self::assertStringContainsString('vfs://root' . \DIRECTORY_SEPARATOR . 'music', $directories[1]);
    }

    public function testCreateDirectory(): void
    {
        $this->files->createDirectory($this->root->url() . \DIRECTORY_SEPARATOR . 'test');

        self::assertDirectoryExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'test'));
        self::assertEquals(0755, $this->root->getChild('test')->getPermissions());

        $this->files->createDirectory($this->root->url() . \DIRECTORY_SEPARATOR . 'test2', ['visibility' => 'private']);

        self::assertEquals(0700, $this->root->getChild('test2')->getPermissions());
    }

    public function testCopy(): void
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));
        $this->root->addChild(new vfsStreamDirectory('copy2'));

        $dir = $this->root->getChild('copy');

        \file_put_contents($dir->url() . \DIRECTORY_SEPARATOR . 'copy.txt', 'copy1');

        $this->files->copy(
            $dir->url() . \DIRECTORY_SEPARATOR . 'copy.txt',
            $this->root->getChild('copy2')->url() . \DIRECTORY_SEPARATOR . 'copy.txt'
        );

        self::assertSame(
            'copy1',
            $this->files->read(
                $this->root->getChild('copy2')->url() . \DIRECTORY_SEPARATOR . 'copy.txt'
            )
        );
    }

    public function testCopyToThrowIOException(): void
    {
        $this->expectException(IOException::class);

        $file = vfsStream::newFile('copy.txt', 0000)
            ->withContent('copy1')
            ->at($this->root);
        $file2 = vfsStream::newFile('copy2.txt', 0000)
            ->at($this->root);

        $this->files->copy(
            $file->url(),
            $file2->url()
        );
    }

    public function testCopyToThrowFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->root->addChild(new vfsStreamDirectory('copy'));

        $this->files->copy(
            \DIRECTORY_SEPARATOR . 'copy.txt',
            $this->root->getChild('copy')->url()
        );
    }

    public function testGetAndSetVisibility(): void
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($dir);

        self::assertSame('0777', $this->files->getVisibility($dir->url()));
        self::assertSame('0666', $this->files->getVisibility($file->url()));

        $this->files->setVisibility($file->url(), 'private');
        $this->files->setVisibility($dir->url(), 'private');

        self::assertSame('private', $this->files->getVisibility($dir->url()));
        self::assertSame('private', $this->files->getVisibility($file->url()));

        $this->files->setVisibility($file->url(), 'public');

        self::assertSame('public', $this->files->getVisibility($file->url()));
    }

    public function testSetVisibilityToThrowInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $this->files->setVisibility($dir->url(), 'exception');
    }

    public function testWrite(): void
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($dir);

        $this->files->write($file->url(), 'copy new');

        self::assertSame('copy new', $this->files->read($file->url()));

        $this->files->write($file->url(), 'copy new visibility', ['visibility' => 'private']);

        self::assertSame('copy new visibility', $this->files->read($file->url()));
        self::assertSame('private', $this->files->getVisibility($file->url()));
    }

    public function testWriteStreamAndReadStream(): void
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $file = vfsStream::newFile('copy.txt')
            ->at($this->root->getChild('copy'));

        $temp = \tmpfile();

        \fwrite($temp, 'dummy');
        \rewind($temp);

        $this->files->writeStream($file->url(), $temp);

        $stream = $this->files->readStream($file->url());

        $contents = \stream_get_contents($stream);
        $size = Util::getStreamSize($stream);

        \fclose($stream);

        self::assertSame(5, $size);
        self::assertSame('dummy', $contents);
        self::assertIsResource($this->files->readStream($file->url()));
    }

    public function testUpdateStream(): void
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($this->root->getChild('copy'));

        self::assertSame('copy', $this->files->read($file->url()));

        $temp = \tmpfile();

        \fwrite($temp, 'dummy');
        \rewind($temp);

        self::assertTrue($this->files->updateStream(
            $file->url(),
            $temp,
            ['visibility' => 'public']
        ));

        $stream = $this->files->readStream($file->url());

        $contents = \stream_get_contents($stream);
        $size = Util::getStreamSize($stream);

        \fclose($stream);

        self::assertSame(5, $size);
        self::assertSame('dummy', $contents);
    }

    public function testGetMimetype(): void
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($dir);

        self::assertSame('text/plain', $this->files->getMimetype($file->url()));
    }

    public function testGetMimetypeToThrowFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->files->getMimetype(vfsStream::url('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'file.php'));
    }

    public function testGetTimestamp(): void
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($dir);

        self::assertSame(\date('F d Y H:i:s', \filemtime($file->url())), $this->files->getTimestamp($file->url()));
    }

    public function testGetTimestampToThrowFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->files->getTimestamp(vfsStream::url('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'file.php'));
    }

    public function testMoveDirectoryMovesEntireDirectory(): void
    {
        $this->root->addChild(new vfsStreamDirectory('tmp'));
        $this->root->addChild(new vfsStreamDirectory('tmp2'));

        $dir = $this->root->getChild('tmp');
        $temp2 = $this->root->getChild('tmp2');

        vfsStream::newFile('foo.txt')
            ->withContent('foo')
            ->at($dir);
        vfsStream::newFile('bar.txt')
            ->withContent('bar')
            ->at($dir);

        $dir->addChild(new vfsStreamDirectory('nested'));
        $dir2 = $dir->getChild('nested');

        vfsStream::newFile('baz.txt')
            ->withContent('baz')
            ->at($dir2);

        $this->files->moveDirectory($dir->url(), $temp2->url());

        self::assertDirectoryExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2'));
        self::assertFileExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'foo.txt');
        self::assertFileExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'bar.txt');
        self::assertDirectoryExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'nested');
        self::assertFileExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'nested' . \DIRECTORY_SEPARATOR . 'baz.txt');
        self::assertDirectoryNotExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp'));
    }

    public function testMoveDirectoryMovesEntireDirectoryAndOverwrites(): void
    {
        $this->root->addChild(new vfsStreamDirectory('tmp'));
        $this->root->addChild(new vfsStreamDirectory('tmp2'));

        $dir = $this->root->getChild('tmp');
        $temp2 = $this->root->getChild('tmp2');

        vfsStream::newFile('foo.txt')
            ->withContent('foo')
            ->at($dir);
        vfsStream::newFile('bar.txt')
            ->withContent('bar')
            ->at($dir);

        $dir->addChild(new vfsStreamDirectory('nested'));
        $dir2 = $dir->getChild('nested');

        vfsStream::newFile('baz.txt')
            ->withContent('baz')
            ->at($dir2);

        vfsStream::newFile('foo2.txt')
            ->withContent('foo2')
            ->at($temp2);
        vfsStream::newFile('bar2.txt')
            ->withContent('bar2')
            ->at($temp2);

        $this->files->moveDirectory($dir->url(), $temp2->url(), ['overwrite' => true]);

        self::assertDirectoryExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2'));
        self::assertFileExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'foo.txt');
        self::assertFileExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'bar.txt');
        self::assertDirectoryExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'nested');
        self::assertFileExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'nested' . \DIRECTORY_SEPARATOR . 'baz.txt');
        self::assertFileNotExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'foo2.txt');
        self::assertFileNotExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'bar2.txt');
        self::assertDirectoryNotExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp'));
    }

    public function testCopyDirectoryReturnsFalseIfSourceIsntDirectory(): void
    {
        self::assertFalse($this->files->copyDirectory(\DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz' . \DIRECTORY_SEPARATOR . 'breeze' . \DIRECTORY_SEPARATOR . 'boom', 'foo'));
    }

    public function testCopyDirectoryMovesEntireDirectory(): void
    {
        $this->root->addChild(new vfsStreamDirectory('tmp'));
        $this->root->addChild(new vfsStreamDirectory('tmp2'));

        $dir = $this->root->getChild('tmp');
        $temp2 = $this->root->getChild('tmp2');

        vfsStream::newFile('foo.txt')
            ->withContent('foo')
            ->at($dir);
        vfsStream::newFile('bar.txt')
            ->withContent('bar')
            ->at($dir);

        $dir->addChild(new vfsStreamDirectory('nested'));
        $dir2 = $dir->getChild('nested');

        vfsStream::newFile('baz.txt')
            ->withContent('baz')
            ->at($dir2);

        $this->files->copyDirectory($dir->url(), $temp2->url());

        self::assertDirectoryExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2'));
        self::assertFileExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'foo.txt');
        self::assertFileExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'bar.txt');
        self::assertDirectoryExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'nested');
        self::assertFileExists(vfsStream::url('root' . \DIRECTORY_SEPARATOR . 'tmp2') . \DIRECTORY_SEPARATOR . 'nested' . \DIRECTORY_SEPARATOR . 'baz.txt');
    }

    public function testFiles(): void
    {
        $this->root->addChild(new vfsStreamDirectory('tmp'));

        $dir = $this->root->getChild('tmp');

        vfsStream::newFile('foo.txt')
            ->withContent('foo')
            ->at($dir);
        vfsStream::newFile('bar.txt')
            ->withContent('bar')
            ->at($dir);

        $dir->addChild(new vfsStreamDirectory('nested'));
        $dir2 = $dir->getChild('nested');

        vfsStream::newFile('baz.txt')
            ->withContent('baz')
            ->at($dir2);

        self::assertContains('bar.txt', $this->files->files($dir->url()));
        self::assertContains('foo.txt', $this->files->files($dir->url()));
        self::assertNotContains('foo2.txt', $this->files->files($dir->url()));
    }

    public function testAllDirectories(): void
    {
        $this->root->addChild(new vfsStreamDirectory('tmp'));
        $this->root->addChild(new vfsStreamDirectory('tmp2'));

        $arr = $this->files->allDirectories($this->root->url());

        self::assertInstanceOf(SplFileInfo::class, $arr[0]);
    }

    public function testAppendOnExistingFile(): void
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);

        $this->files->append($file->url(), ' test');

        self::assertEquals('Foo Bar test', $this->files->read($file->url()));
    }

    public function testAppend(): void
    {
        $url = $this->root->url() . \DIRECTORY_SEPARATOR . 'file.php';

        $this->files->append($url, 'test');

        self::assertEquals('test', $this->files->read($url));
    }

    public function testAppendStreamOnExistingFile(): void
    {
        $file = vfsStream::newFile('copy.txt')->withContent('Foo Bar')->at($this->root);
        $temp = \tmpfile();

        \fwrite($temp, ' dummy');
        \rewind($temp);

        $this->files->appendStream($file->url(), $temp);

        $stream = $this->files->readStream($file->url());

        $contents = \stream_get_contents($stream);
        $size = Util::getStreamSize($stream);

        \fclose($stream);

        self::assertSame(13, $size);
        self::assertSame('Foo Bar dummy', $contents);
        self::assertIsResource($this->files->readStream($file->url()));
    }

    public function testAppendStream(): void
    {
        $url = $this->root->url() . \DIRECTORY_SEPARATOR . 'file.php';
        $temp = \tmpfile();

        \fwrite($temp, 'dummy');
        \rewind($temp);

        $this->files->appendStream($url, $temp);

        $stream = $this->files->readStream($url);

        $contents = \stream_get_contents($stream);
        $size = Util::getStreamSize($stream);

        \fclose($stream);

        self::assertSame(5, $size);
        self::assertSame('dummy', $contents);
        self::assertIsResource($this->files->readStream($url));
    }
}
