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

    public function testReadRetrievesFiles()
    {
        $this->assertEquals('Hello World', $this->adapter->read($this->root . 'test.txt'));
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testReadToThrowException()
    {
        // $this->adapter->read('');
    }

    public function testUpdateStoresFiles()
    {
        $file = $this->root . 'test.txt';

        $this->adapter->update($file, 'Hello World');

        $this->assertStringEqualsFile($file, 'Hello World');
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
        $this->adapter->createDirectory('test-dir');

        $output = $this->adapter->getVisibility('test-dir');

        $this->assertInternalType('array', $output);
        $this->assertArrayHasKey('visibility', $output);
        $this->assertEquals('public', $output['visibility']);

        $this->adapter->deleteDir('test-dir');
    }

    public function testCopy()
    {
        $adapter = $this->adapter;

        $adapter->write('file.ext', 'content', ['visibility' => 'public']);

        $this->assertTrue($adapter->copy('file.ext', 'new.ext'));
        $this->assertTrue($adapter->has('new.ext'));

        $adapter->delete('file.ext');
        $adapter->delete('new.ext');
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\IOException
     */
    public function testCopyToThrowIOException()
    {
        // $this->root->addChild(new vfsStreamDirectory('copy'));

        // $dir = $this->root->getChild('copy');

        // $file = vfsStream::newFile('copy.txt')
        //     ->withContent('copy1')
        //     ->at($dir);

        // $this->adapter->copy(
        //     $dir->url() . '/copy.txt',
        //     $this->root->getChild('copy')->url()
        // );
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testCopyToThrowFileNotFoundException()
    {
        // $this->root->addChild(new vfsStreamDirectory('copy'));

        // $this->adapter->copy(
        //     '/copy.txt',
        //     $this->root->getChild('copy')->url()
        // );
    }

    public function testGetAndSetVisibility()
    {
        // $this->root->addChild(new vfsStreamDirectory('copy'));

        // $dir = $this->root->getChild('copy');

        // $file = vfsStream::newFile('copy.txt')
        //     ->withContent('copy')
        //     ->at($dir);

        // $this->assertSame('public', $this->adapter->getVisibility($dir->url()));
        // $this->assertSame('public', $this->adapter->getVisibility($file->url()));

        // $this->adapter->setVisibility($file->url(), 'private');
        // $this->adapter->setVisibility($dir->url(), 'private');

        // $this->assertSame('private', $this->adapter->getVisibility($dir->url()));
        // $this->assertSame('private', $this->adapter->getVisibility($file->url()));

        // $this->adapter->setVisibility($file->url(), 'public');

        // $this->assertSame('public', $this->adapter->getVisibility($file->url()));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetVisibilityToThrowInvalidArgumentException()
    {
        // $this->root->addChild(new vfsStreamDirectory('copy'));

        // $dir = $this->root->getChild('copy');

        // $this->adapter->setVisibility($dir->url(), 'exception');
    }

    public function testWrite()
    {
        // $this->root->addChild(new vfsStreamDirectory('copy'));

        // $dir = $this->root->getChild('copy');

        // $file = vfsStream::newFile('copy.txt')
        //     ->withContent('copy')
        //     ->at($dir);

        // $this->adapter->write($file->url(), 'copy new');

        // $this->assertSame('copy new', $this->adapter->read($file->url()));

        // $this->adapter->write($file->url(), 'copy new visibility', ['visibility' => 'private']);

        // $this->assertSame('copy new visibility', $this->adapter->read($file->url()));
        // $this->assertSame('private', $this->adapter->getVisibility($file->url()));
    }

    public function testGetMimetype()
    {
        $this->adapter->write('text.txt', 'contents', []);

        $result = $this->adapter->getMimetype('text.txt');

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('mimetype', $result);
        $this->assertEquals('text/plain', $result['mimetype']);
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
        $this->adapter->write('dummy.txt', '1234', []);

        $result = $this->adapter->getTimestamp('dummy.txt');

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertInternalType('int', $result['timestamp']);
    }

    /**
     * @expectedException Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testGetTimestampToThrowFileNotFoundException()
    {
        $this->adapter->getTimestamp($this->root . '/DontExist');
    }
}
