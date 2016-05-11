<?php
namespace Viserio\Filesystem\Tests;

use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use Viserio\Filesystem\Adapters\VfsConnector;
use Viserio\Filesystem\FilesystemAdapter;

class FilesystemAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var Viserio\Filesystem\FilesystemAdapter
     */
    private $files;

    /**
     * Setup the environment.
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();

        $connector = new VfsConnector();

        $this->files = new FilesystemAdapter($connector->connect([]));
    }

    public function testReadRetrievesFiles()
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);
        $this->assertEquals('Foo Bar', $this->files->read($file->url()));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testReadToThrowException()
    {
        $this->files->read(vfsStream::url('foo/bar/tmp/file.php'));
    }

    public function testUpdateStoresFiles()
    {
        $file = vfsStream::newFile('temp.txt')->at($this->root);

        $this->files->update($file->url(), 'Hello World');

        $this->assertStringEqualsFile($file->url(), 'Hello World');
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
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

        $this->assertTrue(is_dir($dir->url()));
        $this->assertFalse($this->files->deleteDirectory($file->url()));

        $this->files->deleteDirectory($dir->url());

        $this->assertFalse(is_dir(vfsStream::url('root/temp')));
        $this->assertFileNotExists($file->url());
    }

    public function testCleanDirectory()
    {
        $this->root->addChild(new vfsStreamDirectory('tempdir'));

        $dir  = $this->root->getChild('tempdir');
        $file = vfsStream::newFile('tempfoo.txt')->withContent('tempfoo')->at($dir);

        $this->assertFalse($this->files->cleanDirectory($file->url()));
        $this->files->cleanDirectory($dir->url());

        $this->assertTrue(is_dir(vfsStream::url('root/tempdir')));
        $this->assertFileNotExists($file->url());
    }

    public function testDeleteRemovesFiles()
    {
        $file = vfsStream::newFile('unlucky.txt')->withContent('So sad')->at($this->root);

        $this->assertTrue($this->files->has($file->url()));

        $this->files->delete($file->url());

        $this->assertFalse($this->files->has($file->url()));
    }

    public function testMoveMovesFiles()
    {
        $file = vfsStream::newFile('pop.txt')->withContent('pop')->at($this->root);
        $rock = $this->root->url() . '/rock.txt';

        $this->files->move($file->url(), $rock);

        $this->assertFileExists($rock);
        $this->assertStringEqualsFile($rock, 'pop');
        $this->assertFileNotExists($this->root->url() . '/pop.txt');
    }

    public function testGetExtensionReturnsExtension()
    {
        $file = vfsStream::newFile('rock.csv')->withContent('pop,rock')->at($this->root);

        $this->assertEquals('csv', $this->files->getExtension($file->url()));
    }

    public function testGetMimeTypeOutputsMimeType()
    {
        if (!class_exists('Finfo')) {
            $this->markTestSkipped('The PHP extension fileinfo is not installed.');
        }

        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        $this->assertEquals('text/plain', $this->files->getMimetype($file->url()));
    }

    public function testGetSizeOutputsSize()
    {
        $content = LargeFileContent::withKilobytes(2);
        $file    = vfsStream::newFile('2kb.txt')->withContent($content)->at($this->root);

        $this->assertEquals($file->size(), $this->files->getSize($file->url()));
    }

    public function testAllFilesFindsFiles()
    {
        $this->root->addChild(new vfsStreamDirectory('languages'));

        $dir   = $this->root->getChild('languages');
        $file1 = vfsStream::newFile('php.txt')->withContent('PHP')->at($dir);
        $file2 = vfsStream::newFile('c.txt')->withContent('C')->at($dir);

        $allFiles = [];

        foreach ($this->files->allFiles($dir->url()) as $file) {
            $allFiles[] = $file;
        }

        $this->assertContains($file1->getName(), $allFiles[0]);
        $this->assertContains($file2->getName(), $allFiles[1]);
    }

    public function testDirectoriesFindsDirectories()
    {
        $this->root->addChild(new vfsStreamDirectory('languages'));
        $this->root->addChild(new vfsStreamDirectory('music'));

        $dir1 = $this->root->getChild('languages');
        $dir2 = $this->root->getChild('music');

        $directories = $this->files->directories($this->root->url());

        $this->assertContains('vfs://root' . DIRECTORY_SEPARATOR . 'languages', $directories[0]);
        $this->assertContains('vfs://root' . DIRECTORY_SEPARATOR . 'music', $directories[1]);
    }

    public function testCreateDirectory()
    {
        $this->files->createDirectory($this->root->url() . '/test');

        $this->assertTrue(is_dir(vfsStream::url('root/test')));
        $this->assertEquals(0755, $this->root->getChild('test')->getPermissions());

        $this->files->createDirectory($this->root->url() . '/test2', ['visibility' => 'private']);

        $this->assertEquals(0700, $this->root->getChild('test2')->getPermissions());
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

        $this->assertSame(
            'copy1',
            $this->files->read(
                $this->root->getChild('copy2')->url() . '/copy.txt'
            )
        );
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\IOException
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
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
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

        $this->assertSame('public', $this->files->getVisibility($dir->url()));
        $this->assertSame('public', $this->files->getVisibility($file->url()));

        $this->files->setVisibility($file->url(), 'private');
        $this->files->setVisibility($dir->url(), 'private');

        $this->assertSame('private', $this->files->getVisibility($dir->url()));
        $this->assertSame('private', $this->files->getVisibility($file->url()));

        $this->files->setVisibility($file->url(), 'public');

        $this->assertSame('public', $this->files->getVisibility($file->url()));
    }

    /**
     * @expectedException InvalidArgumentException
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

        $this->assertSame('copy new', $this->files->read($file->url()));

        $this->files->write($file->url(), 'copy new visibility', ['visibility' => 'private']);

        $this->assertSame('copy new visibility', $this->files->read($file->url()));
        $this->assertSame('private', $this->files->getVisibility($file->url()));
    }

    public function testGetMimetype()
    {
        $this->root->addChild(new vfsStreamDirectory('copy'));

        $dir = $this->root->getChild('copy');

        $file = vfsStream::newFile('copy.txt')
            ->withContent('copy')
            ->at($dir);

        $this->assertSame('text/plain', $this->files->getMimetype($file->url()));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
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

        $this->assertSame(date('F d Y H:i:s', filemtime($file->url())), $this->files->getTimestamp($file->url()));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testGetTimestampToThrowFileNotFoundException()
    {
        $this->files->getTimestamp(vfsStream::url('foo/bar/tmp/file.php'));
    }
}
