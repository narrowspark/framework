<?php
namespace Viserio\Filesystem\Tests;

use org\bovigo\vfs\content\LargeFileContent;
use Viserio\Filesystem\Adapters\LocalConnector;
use Viserio\Filesystem\FilesystemAdapter;

class FilesystemAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $root;

    /**
     * @var Viserio\Filesystem\FilesystemAdapter
     */
    private $adapter;

    /**
     * Setup the environment.
     */
    public function setUp()
    {
        $this->root = __DIR__ . '/stubs/';

        $connector = new LocalConnector();

        $this->adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]));
    }

    public function tearDown()
    {
        $this->delTree($this->root);
    }

    public function testGetDriver()
    {
        $this->assertInstanceOf('\League\Flysystem\AdapterInterface', $this->adapter->getDriver());
    }

    public function testIsDir()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');

        $this->assertTrue($adapter->isDirectory('test-dir'));
    }

    public function testReadRetrievesFiles()
    {
        $adapter = $this->adapter;

        $adapter->write('test.txt', 'Hello World');

        $this->assertEquals('Hello World', $adapter->read('test.txt'));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testReadToThrowException()
    {
        $this->adapter->read('test2.txt');
    }

    public function testUpdateStoresFiles()
    {
        $adapter = $this->adapter;

        $adapter->write('test.txt', 'test');

        $adapter->update('test.txt', 'Hello World');

        $this->assertEquals('Hello World', $adapter->read('test.txt'));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testUpdateToThrowException()
    {
        $this->adapter->update($this->root . 'TestDontExists.txt', 'Hello World');
    }

    public function testDeleteDirectory()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('delete-dir');
        $adapter->write('/delete-dir/delete.txt', 'delete');

        $this->assertTrue(is_dir($this->root . '/delete-dir'));
        $this->assertFalse($adapter->deleteDirectory($this->root . '/delete-dir/delete.txt'));

        $adapter->deleteDirectory('delete-dir');

        $this->assertFalse(is_dir($this->root . '/delete-dir'));
        $this->assertFileNotExists($this->root . '/delete-dir/delete.txt');
    }

    public function testCleanDirectory()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('tempdir');
        $adapter->write('tempdir/tempfoo.txt', 'tempfoo');

        $this->assertFalse($adapter->cleanDirectory('tempdir/tempfoo.txt'));
        $this->adapter->cleanDirectory('tempdir');

        $this->assertTrue(is_dir($this->root . '/tempdir'));
        $this->assertFileNotExists($this->root . '/tempfoo.txt');
    }

    public function testDeleteRemovesFiles()
    {
        $adapter = $this->adapter;

        $adapter->write('unlucky.txt', 'delete');

        $this->assertTrue($adapter->has('unlucky.txt'));

        $adapter->delete(['unlucky.txt']);

        $this->assertFalse($adapter->has('unlucky.txt'));
    }

    public function testMoveMovesFiles()
    {
        $adapter = $this->adapter;

        $adapter->write('pop.txt', 'delete');

        $adapter->move('pop.txt', 'rock.txt');

        $this->assertFileExists($this->root . '/rock.txt');
        $this->assertStringEqualsFile($this->root . '/rock.txt', 'delete');
        $this->assertFileNotExists('pop.txt');
    }

    public function testGetMimeTypeOutputsMimeType()
    {
        $adapter = $this->adapter;

        $adapter->write('foo.txt', 'test');

        $this->assertEquals('text/plain', $adapter->getMimetype('foo.txt'));
    }

    public function testGetSizeOutputsSize()
    {
        $content = LargeFileContent::withKilobytes(2);
        $adapter = $this->adapter;

        $adapter->write('2kb.txt', $content->content());

        $this->assertEquals(filesize($this->root . '2kb.txt'), $adapter->getSize('2kb.txt'));
    }

    public function testAllFilesFindsFiles()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');

        $allFiles = $this->adapter->allFiles('languages');

        $this->assertTrue(in_array('languages/c.txt', $allFiles));
        $this->assertTrue(in_array('languages/php.txt', $allFiles));
    }

    public function testDirectoriesFindsDirectories()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test');
        $adapter->createDirectory('test/languages');
        $adapter->createDirectory('test/music');

        $directories = $adapter->directories('test');

        $this->assertContains('test/languages', $directories[0]);
        $this->assertContains('test/music', $directories[1]);
    }

    public function testCreateDirectory()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');

        $this->assertSame('public', $adapter->getVisibility('test-dir'));
    }

    public function testCopy()
    {
        $adapter = $this->adapter;

        $adapter->write('file.ext', 'content', ['visibility' => 'public']);

        $this->assertTrue($adapter->copy('file.ext', 'new.ext'));
        $this->assertTrue($adapter->has('new.ext'));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\IOException
     */
    public function testCopyToThrowIOException()
    {
        $adapter = $this->adapter;

        $adapter->write('file.ext', 'content', ['visibility' => 'private']);

        $adapter->copy('file.ext', '/test/');
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testCopyToThrowFileNotFoundException()
    {
        $adapter = $this->adapter;

        $adapter->copy('notexist.test', 'copy');
    }

    public function testGetAndSetVisibility()
    {
        $adapter = $this->adapter;

        $adapter->write('copy.txt', 'content');

        $this->assertSame('public', $this->adapter->getVisibility('copy.txt'));

        $this->adapter->setVisibility('copy.txt', 'public');

        $this->assertSame('public', $this->adapter->getVisibility('copy.txt'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetVisibilityToThrowInvalidArgumentException()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');
        $adapter->setVisibility('test-dir', 'exception');
    }

    public function testGetMimetype()
    {
        $this->adapter->write('text.txt', 'contents', []);

        $this->assertEquals('text/plain', $this->adapter->getMimetype('text.txt'));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testGetMimetypeToThrowFileNotFoundException()
    {
        $this->adapter->getMimetype($this->root . '/DontExist');
    }

    public function testGetTimestamp()
    {
        $adapter = $this->adapter;

        $adapter->write('dummy.txt', '1234');

        $this->assertInternalType('int', $adapter->getTimestamp('dummy.txt'));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testGetTimestampToThrowFileNotFoundException()
    {
        $this->adapter->getTimestamp('/DontExist');
    }

    public function testFiles()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');
        $adapter->createDirectory('languages/lang');
        $adapter->write('languages/lang/c.txt', 'c');

        $this->assertTrue(in_array('languages/c.txt', $this->adapter->files('languages')));
        $this->assertTrue(in_array('languages/php.txt', $this->adapter->files('languages')));
        $this->assertFalse(in_array('languages/lang/c.txt', $this->adapter->files('languages')));
    }

    // public function testMoveDirectoryMovesEntireDirectory()
    // {
    //     $this->root->addChild(new vfsStreamDirectory('tmp'));
    //     $this->root->addChild(new vfsStreamDirectory('tmp2'));

    //     $dir = $this->root->getChild('tmp');
    //     $temp2 = $this->root->getChild('tmp2');

    //     $file = vfsStream::newFile('foo.txt')
    //         ->withContent('foo')
    //         ->at($dir);
    //     $file2 = vfsStream::newFile('bar.txt')
    //         ->withContent('bar')
    //         ->at($dir);

    //     $dir->addChild(new vfsStreamDirectory('nested'));
    //     $dir2 = $dir->getChild('nested');

    //     $file3 = vfsStream::newFile('baz.txt')
    //         ->withContent('baz')
    //         ->at($dir2);

    //     $this->adapter->moveDirectory($dir->url(), $temp2->url());

    //     $this->assertTrue(is_dir(vfsStream::url('root/tmp2')));
    //     $this->assertFileExists(vfsStream::url('root/tmp2') . '/foo.txt');
    //     $this->assertFileExists(vfsStream::url('root/tmp2') . '/bar.txt');
    //     $this->assertTrue(is_dir(vfsStream::url('root/tmp2') . '/nested'));
    //     $this->assertFileExists(vfsStream::url('root/tmp2') . '/nested/baz.txt');
    //     $this->assertFalse(is_dir(vfsStream::url('root/tmp')));
    // }

    // public function testMoveDirectoryMovesEntireDirectoryAndOverwrites()
    // {
    //     $this->root->addChild(new vfsStreamDirectory('tmp'));
    //     $this->root->addChild(new vfsStreamDirectory('tmp2'));

    //     $dir = $this->root->getChild('tmp');
    //     $temp2 = $this->root->getChild('tmp2');

    //     vfsStream::newFile('foo.txt')
    //         ->withContent('foo')
    //         ->at($dir);
    //     vfsStream::newFile('bar.txt')
    //         ->withContent('bar')
    //         ->at($dir);

    //     $dir->addChild(new vfsStreamDirectory('nested'));
    //     $dir2 = $dir->getChild('nested');

    //     vfsStream::newFile('baz.txt')
    //         ->withContent('baz')
    //         ->at($dir2);

    //     vfsStream::newFile('foo2.txt')
    //         ->withContent('foo2')
    //         ->at($temp2);
    //     vfsStream::newFile('bar2.txt')
    //         ->withContent('bar2')
    //         ->at($temp2);

    //     $this->adapter->moveDirectory($dir->url(), $temp2->url(), ['overwrite' => true]);

    //     $this->assertTrue(is_dir(vfsStream::url('root/tmp2')));
    //     $this->assertFileExists(vfsStream::url('root/tmp2') . '/foo.txt');
    //     $this->assertFileExists(vfsStream::url('root/tmp2') . '/bar.txt');
    //     $this->assertTrue(is_dir(vfsStream::url('root/tmp2') . '/nested'));
    //     $this->assertFileExists(vfsStream::url('root/tmp2') . '/nested/baz.txt');
    //     $this->assertFileNotExists(vfsStream::url('root/tmp2') . '/foo2.txt');
    //     $this->assertFileNotExists(vfsStream::url('root/tmp2') . '/bar2.txt');
    //     $this->assertFalse(is_dir(vfsStream::url('root/tmp')));
    // }

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
