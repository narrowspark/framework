<?php
namespace Viserio\Filesystem\Tests;

use org\bovigo\vfs\content\LargeFileContent;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Viserio\Filesystem\Filesystem;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    /**
     * @var Viserio\Filesystem\Filesystem
     */
    private $files;

    /**
     * Setup the environment.
     */
    public function setUp()
    {
        $this->root = vfsStream::setup();
        $this->files = new Filesystem();
    }

    public function testReadRetrievesFiles()
    {
        $file = vfsStream::newFile('temp.txt')->withContent('Foo Bar')->at($this->root);

        $this->assertEquals('Foo Bar', $this->files->read($file->url()));
    }

    public function testUpdateStoresFiles()
    {
        $file = vfsStream::newFile('temp.txt')->at($this->root);

        $this->files->update($file->url(), 'Hello World');

        $this->assertStringEqualsFile($file->url(), 'Hello World');
    }

    public function testDeleteDirectory()
    {
        $this->root->addChild(new vfsStreamDirectory('temp'));

        $dir  = $this->root->getChild('temp');
        $file = vfsStream::newFile('bar.txt')->withContent('bar')->at($dir);

        $this->assertTrue(is_dir($dir->url()));

        $this->files->deleteDirectory($dir->url());

        $this->assertFalse(is_dir($dir->url()));
        $this->assertFileNotExists($file->url());
    }

    public function testCleanDirectory()
    {
        $this->root->addChild(new vfsStreamDirectory('party'));

        $dir  = $this->root->getChild('party');
        $file = vfsStream::newFile('soda.txt')->withContent('party')->at($dir);

        $this->files->cleanDirectory($dir->url());

        $this->assertTrue(is_dir($dir->url()));
        $this->assertFileNotExists($file->url());
    }

    public function testDeleteRemovesFiles()
    {
        $file = vfsStream::newFile('unlucky.txt')->withContent('So sad')->at($this->root);

        $this->assertTrue($this->files->exists($file->url()));

        $this->files->delete($file->url());

        $this->assertFalse($this->files->exists($file->url()));
    }

    /**
     * @expectedException League\Flysystem\FileNotFoundException
     */
    public function testGetRequireThrowsExceptionNonexisitingFile()
    {
        $this->files->getRequire(vfsStream::url('foo/bar/tmp/file.php'));
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

    /**
     * @requires extension fileinfo
     */
    public function testGetMimeTypeOutputsMimeType()
    {
        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        $this->assertEquals('text/plain', $this->files->getMimetype($file->url()));
    }

    public function testGetSizeOutputsSize()
    {
        $content = LargeFileContent::withKilobytes(2);
        $file    = vfsStream::newFile('2kb.txt')->withContent($content)->at($this->root);

        $this->assertEquals($file->size(), $this->files->getSize($file->url()));
    }

    public function testIsWritable()
    {
        $file = vfsStream::newFile('foo.txt', 0444)->withContent('foo')->at($this->root);

        $this->assertFalse($this->files->isWritable($file->url()));

        $file->chmod(0777);

        $this->assertTrue($this->files->isWritable($file->url()));
    }

    public function testIsFile()
    {
        $this->root->addChild(new vfsStreamDirectory('assets'));
        $dir  = $this->root->getChild('assets');
        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        $this->assertFalse($this->files->isFile($dir->url()));
        $this->assertTrue($this->files->isFile($file->url()));
    }

    public function testIsDirectory()
    {
        $this->root->addChild(new vfsStreamDirectory('assets'));
        $dir  = $this->root->getChild('assets');
        $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        $this->assertTrue($this->files->isDirectory($dir->url()));
        $this->assertFalse($this->files->isDirectory($file->url()));
    }

    public function testGlobFindsFiles()
    {
        file_put_contents(__DIR__ . '/foo.txt', 'foo');
        file_put_contents(__DIR__ . '/bar.txt', 'bar');

        $glob = $this->files->glob(__DIR__ . '/*.txt');

        $this->assertContains(__DIR__ . '/foo.txt', $glob);
        $this->assertContains(__DIR__ . '/bar.txt', $glob);

        @unlink(__DIR__ . '/foo.txt');
        @unlink(__DIR__ . '/bar.txt');
    }

    public function testAllFilesFindsFiles()
    {
        $this->root->addChild(new vfsStreamDirectory('languages'));

        $dir   = $this->root->getChild('languages');
        $file1 = vfsStream::newFile('php.txt')->withContent('PHP')->at($dir);
        $file2 = vfsStream::newFile('c.txt')->withContent('C')->at($dir);

        $allFiles = [];

        foreach ($this->files->allFiles($dir->url()) as $file) {
            $allFiles[] = $file->getFilename();
        }

        $this->assertContains($file1->getName(), $allFiles);
        $this->assertContains($file2->getName(), $allFiles);
    }

    public function testDirectoriesFindsDirectories()
    {
        $this->root->addChild(new vfsStreamDirectory('languages'));
        $this->root->addChild(new vfsStreamDirectory('music'));

        $dir1 = $this->root->getChild('languages');
        $dir2 = $this->root->getChild('music');

        $directories = $this->files->directories($this->root->url());

        $this->assertContains($dir1->url(), $directories);
        $this->assertContains($dir2->url(), $directories);
    }
}
