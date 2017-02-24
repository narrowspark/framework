<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests;

use League\Flysystem\Util;
use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use Viserio\Component\Filesystem\Filesystem;

class FilesystemTest extends TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStream
     */
    private $root;

    /**
     * @var \Viserio\Component\Filesystem\Filesystem
     */
    private $files;

    /**
     * Setup the environment.
     */
    public function setUp()
    {
        $this->root  = vfsStream::setup();
        $this->files = new Filesystem();
    }

    public function testReadRetrievesFiles()
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);

        self::assertEquals('Foo Bar', $this->files->read($file->url()));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testReadStreamToThrowException()
    {
        $this->files->readStream(vfsStream::url('foo/bar/tmp/file.php'));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testReadToThrowException()
    {
        $this->files->read(vfsStream::url('foo/bar/tmp/file.php'));
    }

    public function testUpdateStoresFiles()
    {
        $file = vfsStream::newFile('temp.txt')->at($this->root);

        $this->files->update($file->url(), 'Hello World');
        $this->files->update($file->url(), 'Hello World2');

        self::assertStringEqualsFile($file->url(), 'Hello World2');
    }

    public function testPutStoresFiles()
    {
        $file = vfsStream::newFile('temp.txt')->at($this->root);

        $this->files->put($file->url(), 'Hello World');

        self::assertStringEqualsFile($file->url(), 'Hello World');
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testUpdateToThrowException()
    {
        $this->files->update(vfsStream::url('foo/bar/tmp/file.php'), 'Hello World');
    }

    public function testDeleteDirectory()
    {
        $this->root->addChild(new vfsStreamDirectory('temp'));

        $dir  = $this->root->getChild('temp');
        $file = vfsStream::newFile('bar.txt')->withContent('bar')->at($dir);

        self::assertTrue(is_dir($dir->url()));
        self::assertFalse($this->files->deleteDirectory($file->url()));

        $this->files->deleteDirectory($dir->url());

        self::assertFalse(is_dir(vfsStream::url('root/temp')));
        self::assertFileNotExists($file->url());
    }

    public function testCleanDirectory()
    {
        $this->root->addChild(new vfsStreamDirectory('tempdir'));

        $dir  = $this->root->getChild('tempdir');
        $file = vfsStream::newFile('tempfoo.txt')->withContent('tempfoo')->at($dir);

        self::assertFalse($this->files->cleanDirectory($file->url()));
        $this->files->cleanDirectory($dir->url());

        self::assertTrue(is_dir(vfsStream::url('root/tempdir')));
        self::assertFileNotExists($file->url());
    }

    public function testDeleteRemovesFiles()
    {
        $file = vfsStream::newFile('unlucky.txt')->withContent('So sad')->at($this->root);

        self::assertTrue($this->files->has($file->url()));

        $this->files->delete([$file->url()]);

        self::assertFalse($this->files->has($file->url()));
    }

    public function testMoveMovesFiles()
    {
        $file = vfsStream::newFile('pop.txt')->withContent('pop')->at($this->root);
        $rock = $this->root->url() . '/rock.txt';

        $this->files->move($file->url(), $rock);

        self::assertFileExists($rock);
        self::assertStringEqualsFile($rock, 'pop');
        self::assertFileNotExists($this->root->url() . '/pop.txt');
    }

    public function testGetExtensionReturnsExtension()
    {
        $file = vfsStream::newFile('rock.csv')->withContent('pop,rock')->at($this->root);

        self::assertEquals('csv', $this->files->getExtension($file->url()));
    }

    public function testGetMimeTypeOutputsMimeType()
    {
        if (! class_exists('Finfo')) {
            $this->markTestSkipped('The PHP extension fileinfo is not installed.');
        }

        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        self::assertEquals('text/plain', $this->files->getMimetype($file->url()));
    }

    public function testGetSizeOutputsSize()
    {
        $content = LargeFileContent::withKilobytes(2);
        $file    = vfsStream::newFile('2kb.txt')->withContent($content)->at($this->root);

        self::assertEquals($file->size(), $this->files->getSize($file->url()));
    }

    public function testIsDirectory()
    {
        $this->root->addChild(new vfsStreamDirectory('assets'));
        $dir  = $this->root->getChild('assets');
        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        self::assertTrue($this->files->isDirectory($dir->url()));
        self::assertFalse($this->files->isDirectory($file->url()));
    }

    public function testAllFilesFindsFiles()
    {
        $this->root->addChild(new vfsStreamDirectory('languages'));

        $dir   = $this->root->getChild('languages');
        $file1 = vfsStream::newFile('php.txt')->withContent('PHP')->at($dir);
        $file2 = vfsStream::newFile('c.txt')->withContent('C')->at($dir);

        $allFiles = $this->files->allFiles($dir->url());

        self::assertContains($file1->getName(), $allFiles[0]);
        self::assertContains($file2->getName(), $allFiles[1]);
    }

    public function testDirectoriesFindsDirectories()
    {
        $this->root->addChild(new vfsStreamDirectory('languages'));
        $this->root->addChild(new vfsStreamDirectory('music'));

        $dir1 = $this->root->getChild('languages');
        $dir2 = $this->root->getChild('music');

        $directories = $this->files->directories($this->root->url());

        self::assertContains('vfs://root' . DIRECTORY_SEPARATOR . 'languages', $directories[0]);
        self::assertContains('vfs://root' . DIRECTORY_SEPARATOR . 'music', $directories[1]);
    }

    public function testCreateDirectory()
    {
        $this->files->createDirectory($this->root->url() . '/test');

        self::assertTrue(is_dir(vfsStream::url('root/test')));
        self::assertEquals(0755, $this->root->getChild('test')->getPermissions());

        $this->files->createDirectory($this->root->url() . '/test2', ['visibility' => 'private']);

        self::assertEquals(0700, $this->root->getChild('test2')->getPermissions());
    }

    public function testCopy()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));
        $this->root->addChild(new vfsStreamDirectory('copy2'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy1')
            ->at($dir);

        $this->files->copy(
            $dir->url() . '/copy.txt',
            $this->root->getChild('copy2')->url() . '/copy.txt'
        );

        self::assertSame(
            'copy1',
            $this->files->read(
                $this->root->getChild('copy2')->url() . '/copy.txt'
            )
        );
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\IOException
     */
    public function testCopyToThrowIOException()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy1')
            ->at($dir);

        $this->files->copy(
            $dir->url() . '/copy.txt',
            $this->root->getChild('copy')->url()
        );
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testCopyToThrowFileNotFoundException()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $this->files->copy(
            '/copy.txt',
            $this->root->getChild('copy')->url()
        );
    }

    public function testGetAndSetVisibility()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($dir);

        self::assertSame('public', $this->files->getVisibility($dir->url()));
        self::assertSame('public', $this->files->getVisibility($file->url()));

        $this->files->setVisibility($file->url(), 'private');
        $this->files->setVisibility($dir->url(), 'private');

        self::assertSame('private', $this->files->getVisibility($dir->url()));
        self::assertSame('private', $this->files->getVisibility($file->url()));

        $this->files->setVisibility($file->url(), 'public');

        self::assertSame('public', $this->files->getVisibility($file->url()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetVisibilityToThrowInvalidArgumentException()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $this->files->setVisibility($dir->url(), 'exception');
    }

    public function testWrite()
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

    public function testWriteStreamAndReadStream()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $file = vfsStream::newFile('copy.txt')
            ->at($this->root->getChild('copy'));

        $temp = tmpfile();
        fwrite($temp, 'dummy');
        rewind($temp);

        $this->files->writeStream($file->url(), $temp);

        $stream = $this->files->readStream($file->url());

        $contents = stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        fclose($stream);

        self::assertSame(5, $size);
        self::assertSame('dummy', $contents);
        self::assertInternalType('resource', $this->files->readStream($file->url()));
    }

    public function testUpdateStream()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($this->root->getChild('copy'));

        self::assertSame('copy', $this->files->read($file->url()));

        $temp = tmpfile();

        fwrite($temp, 'dummy');
        rewind($temp);

        self::assertTrue($this->files->updateStream(
            $file->url(),
            $temp,
            ['visibility' => 'public']
        ));

        $stream = $this->files->readStream($file->url());

        $contents = stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        fclose($stream);

        self::assertSame(5, $size);
        self::assertSame('dummy', $contents);
    }

    public function testGetMimetype()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($dir);

        self::assertSame('text/plain', $this->files->getMimetype($file->url()));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testGetMimetypeToThrowFileNotFoundException()
    {
        $this->files->getMimetype(vfsStream::url('foo/bar/tmp/file.php'));
    }

    public function testGetTimestamp()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($dir);

        self::assertSame(date('F d Y H:i:s', filemtime($file->url())), $this->files->getTimestamp($file->url()));
    }

    /**
     * @expectedException \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testGetTimestampToThrowFileNotFoundException()
    {
        $this->files->getTimestamp(vfsStream::url('foo/bar/tmp/file.php'));
    }

    public function testMoveDirectoryMovesEntireDirectory()
    {
        $this->root->addChild(new vfsStreamDirectory('tmp'));
        $this->root->addChild(new vfsStreamDirectory('tmp2'));

        $dir   = $this->root->getChild('tmp');
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

        self::assertTrue(is_dir(vfsStream::url('root/tmp2')));
        self::assertFileExists(vfsStream::url('root/tmp2') . '/foo.txt');
        self::assertFileExists(vfsStream::url('root/tmp2') . '/bar.txt');
        self::assertTrue(is_dir(vfsStream::url('root/tmp2') . '/nested'));
        self::assertFileExists(vfsStream::url('root/tmp2') . '/nested/baz.txt');
        self::assertFalse(is_dir(vfsStream::url('root/tmp')));
    }

    public function testMoveDirectoryMovesEntireDirectoryAndOverwrites()
    {
        $this->root->addChild(new vfsStreamDirectory('tmp'));
        $this->root->addChild(new vfsStreamDirectory('tmp2'));

        $dir   = $this->root->getChild('tmp');
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

        self::assertTrue(is_dir(vfsStream::url('root/tmp2')));
        self::assertFileExists(vfsStream::url('root/tmp2') . '/foo.txt');
        self::assertFileExists(vfsStream::url('root/tmp2') . '/bar.txt');
        self::assertTrue(is_dir(vfsStream::url('root/tmp2') . '/nested'));
        self::assertFileExists(vfsStream::url('root/tmp2') . '/nested/baz.txt');
        self::assertFileNotExists(vfsStream::url('root/tmp2') . '/foo2.txt');
        self::assertFileNotExists(vfsStream::url('root/tmp2') . '/bar2.txt');
        self::assertFalse(is_dir(vfsStream::url('root/tmp')));
    }

    public function testCopyDirectoryReturnsFalseIfSourceIsntDirectory()
    {
        self::assertFalse($this->files->copyDirectory('/foo/bar/baz/breeze/boom', 'foo'));
    }

    public function testCopyDirectoryMovesEntireDirectory()
    {
        $this->root->addChild(new vfsStreamDirectory('tmp'));
        $this->root->addChild(new vfsStreamDirectory('tmp2'));

        $dir   = $this->root->getChild('tmp');
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

        self::assertTrue(is_dir(vfsStream::url('root/tmp2')));
        self::assertFileExists(vfsStream::url('root/tmp2') . '/foo.txt');
        self::assertFileExists(vfsStream::url('root/tmp2') . '/bar.txt');
        self::assertTrue(is_dir(vfsStream::url('root/tmp2') . '/nested'));
        self::assertFileExists(vfsStream::url('root/tmp2') . '/nested/baz.txt');
    }

    public function testFiles()
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

        self::assertTrue(in_array('bar.txt', $this->files->files($dir->url())));
        self::assertTrue(in_array('foo.txt', $this->files->files($dir->url())));
        self::assertFalse(in_array('foo2.txt', $this->files->files($dir->url())));
    }

    public function testAllDirectories()
    {
        $this->root->addChild(new vfsStreamDirectory('tmp'));
        $this->root->addChild(new vfsStreamDirectory('tmp2'));

        $arr = $this->files->allDirectories($this->root->url());

        self::assertInstanceOf(SplFileInfo::class, $arr[0]);
    }
}
