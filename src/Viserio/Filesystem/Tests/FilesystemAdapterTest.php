<?php
namespace Viserio\Filesystem\Tests;

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

    public function testReadRetrievesFiles()
    {
        $this->adapter->write('test.txt', 'Hello World');

        $this->assertEquals('Hello World', $this->adapter->read('test.txt'));
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
        $this->adapter->write('test.txt', 'test');

        $this->adapter->update('test.txt', 'Hello World');

        $this->assertEquals('Hello World', $this->adapter->read('test.txt'));
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
        // $this->root->addChild(new vfsStreamDirectory('temp'));

        // $dir  = $this->root->getChild('temp');
        // $file = vfsStream::newFile('bar.txt')->withContent('bar')->at($dir);

        // $this->assertTrue(is_dir($dir->url()));
        // $this->assertFalse($this->adapter->deleteDirectory($file->url()));

        // $this->adapter->deleteDirectory($dir->url());

        // $this->assertFalse(is_dir(vfsStream::url('root/temp')));
        // $this->assertFileNotExists($file->url());
    }

    public function testCleanDirectory()
    {
        // $this->root->addChild(new vfsStreamDirectory('tempdir'));

        // $dir  = $this->root->getChild('tempdir');
        // $file = vfsStream::newFile('tempfoo.txt')->withContent('tempfoo')->at($dir);

        // $this->assertFalse($this->adapter->cleanDirectory($file->url()));
        // $this->adapter->cleanDirectory($dir->url());

        // $this->assertTrue(is_dir(vfsStream::url('root/tempdir')));
        // $this->assertFileNotExists($file->url());
    }

    public function testDeleteRemovesFiles()
    {
        // $file = vfsStream::newFile('unlucky.txt')->withContent('So sad')->at($this->root);

        // $this->assertTrue($this->adapter->has($file->url()));

        // $this->adapter->delete($file->url());

        // $this->assertFalse($this->adapter->has($file->url()));
    }

    public function testMoveMovesFiles()
    {
        // $file = vfsStream::newFile('pop.txt')->withContent('pop')->at($this->root);
        // $rock = $this->root->url() . '/rock.txt';

        // $this->adapter->move($file->url(), $rock);

        // $this->assertFileExists($rock);
        // $this->assertStringEqualsFile($rock, 'pop');
        // $this->assertFileNotExists($this->root->url() . '/pop.txt');
    }

    public function testGetMimeTypeOutputsMimeType()
    {
        // if (!class_exists('Finfo')) {
        //     $this->markTestSkipped('The PHP extension fileinfo is not installed.');
        // }

        // $file = vfsStream::newFile('foo.txt')->withContent('foo')->at($this->root);

        // $this->assertEquals('text/plain', $this->adapter->getMimetype($file->url()));
    }

    public function testGetSizeOutputsSize()
    {
        // $content = LargeFileContent::withKilobytes(2);
        // $file    = vfsStream::newFile('2kb.txt')->withContent($content)->at($this->root);

        // $this->assertEquals($file->size(), $this->adapter->getSize($file->url()));
    }

    public function testAllFilesFindsFiles()
    {
        // $this->root->addChild(new vfsStreamDirectory('languages'));

        // $dir   = $this->root->getChild('languages');
        // $file1 = vfsStream::newFile('php.txt')->withContent('PHP')->at($dir);
        // $file2 = vfsStream::newFile('c.txt')->withContent('C')->at($dir);

        // $allFiles = [];

        // foreach ($this->adapter->allFiles($dir->url()) as $file) {
        //     $allFiles[] = $file;
        // }

        // $this->assertContains($file1->getName(), $allFiles[0]);
        // $this->assertContains($file2->getName(), $allFiles[1]);
    }

    public function testDirectoriesFindsDirectories()
    {
        // $this->root->addChild(new vfsStreamDirectory('languages'));
        // $this->root->addChild(new vfsStreamDirectory('music'));

        // $dir1 = $this->root->getChild('languages');
        // $dir2 = $this->root->getChild('music');

        // $directories = $this->adapter->directories($this->root->url());

        // $this->assertContains('vfs://root' . DIRECTORY_SEPARATOR . 'languages', $directories[0]);
        // $this->assertContains('vfs://root' . DIRECTORY_SEPARATOR . 'music', $directories[1]);
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

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
