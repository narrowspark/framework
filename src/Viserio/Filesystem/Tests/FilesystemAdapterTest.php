<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests;

use League\Flysystem\Util;
use org\bovigo\vfs\content\LargeFileContent;
use Viserio\Filesystem\Adapters\LocalConnector;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Filesystem\FilesystemAdapter;

class FilesystemAdapterTest extends \PHPUnit_Framework_TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var string
     */
    private $root;

    /**
     * @var \Viserio\Filesystem\FilesystemAdapter
     */
    private $adapter;

    /**
     * Setup the environment.
     */
    public function setUp()
    {
        $this->root = __DIR__ . '/stubs';

        $connector = new LocalConnector();

        $this->adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]), []);
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

    public function testPutStoresFiles()
    {
        $adapter = $this->adapter;

        $adapter->put('test.txt', 'Hello World');

        $this->assertEquals('Hello World', $adapter->read('test.txt'));

        $adapter->put('test.txt', 'Hello World 2');

        $this->assertEquals('Hello World 2', $adapter->read('test.txt'));
    }

    /**
     * @expectedException \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testReadToThrowException()
    {
        $this->adapter->read('test2.txt');
    }

    /**
     * @expectedException \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testReadStreamToThrowException()
    {
        $this->adapter->readStream('foo/bar/tmp/file.php');
    }

    public function testUpdateStoresFiles()
    {
        $adapter = $this->adapter;

        $adapter->write('test.txt', 'test');
        $adapter->update('test.txt', 'Hello World');

        $this->assertEquals('Hello World', $adapter->read('test.txt'));
    }

    /**
     * @expectedException \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     */
    public function testUpdateToThrowException()
    {
        $this->adapter->update($this->root . 'TestDontExists.txt', 'Hello World');
    }

    public function testUpdateStream()
    {
        $adapter = $this->adapter;

        $temp = tmpfile();

        fwrite($temp, 'copy');
        rewind($temp);

        $adapter->writeStream('stream.txt', $temp);

        fwrite($temp, 'dummy');
        rewind($temp);

        $this->assertTrue($adapter->updateStream('stream.txt', $temp, ['visibility' => 'public']));

        $stream = $adapter->readStream('stream.txt');

        $contents = stream_get_contents($stream);
        $size = Util::getStreamSize($stream);

        fclose($stream);
        fclose($temp);

        $this->assertSame(9, $size);
        $this->assertSame('copydummy', $contents);
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

        $this->assertEquals(filesize($this->root . '/2kb.txt'), $adapter->getSize('2kb.txt'));
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
     * @expectedException \Viserio\Contracts\Filesystem\Exception\IOException
     */
    public function testCopyToThrowIOException()
    {
        $adapter = $this->adapter;

        $adapter->write('file.ext', 'content', ['visibility' => 'private']);

        $adapter->copy('file.ext', '/test/');
    }

    /**
     * @expectedException \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
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
     * @expectedException \InvalidArgumentException
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
     * @expectedException \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
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
     * @expectedException \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
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

    public function testCopyDirectoryMovesEntireDirectory()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->createDirectory('root');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');
        $adapter->createDirectory('languages/lang');
        $adapter->write('languages/lang/c.txt', 'c');

        $adapter->copyDirectory('languages', 'root');

        $this->assertFalse($this->adapter->copyDirectory('dontmove', 'code'));
        $this->assertSame($this->adapter->getVisibility('languages'), $this->adapter->getVisibility('root'));
        $this->assertTrue(in_array('root/c.txt', $this->adapter->files('root')));
        $this->assertTrue(in_array('root/php.txt', $this->adapter->files('root')));
        $this->assertTrue(in_array('root/lang/c.txt', $this->adapter->files('root/lang')));
    }

    public function testMoveDirectoryMovesEntireDirectory()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->createDirectory('root');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');
        $adapter->createDirectory('languages/lang');
        $adapter->write('languages/lang/c.txt', 'c');

        $this->adapter->moveDirectory('languages', 'root');

        $this->assertTrue(in_array('root/c.txt', $this->adapter->files('root')));
        $this->assertTrue(in_array('root/php.txt', $this->adapter->files('root')));
        $this->assertTrue(in_array('root/lang/c.txt', $this->adapter->files('root/lang')));
        $this->assertFalse(in_array('languages/c.txt', $this->adapter->files('languages')));
        $this->assertFalse(in_array('languages/php.txt', $this->adapter->files('languages')));
        $this->assertFalse(in_array('languages/lang/c.txt', $this->adapter->files('languages/lang')));
    }

    public function testMoveDirectoryMovesEntireDirectoryAndOverwrites()
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');
        $adapter->createDirectory('languages/lang');
        $adapter->write('languages/lang/c.txt', 'c');

        $adapter->createDirectory('code');
        $adapter->write('code/javascript.txt', 'javascript');

        $this->adapter->moveDirectory('languages', 'code', ['overwrite' => true]);

        $this->assertTrue($this->adapter->isWritable('code'));
        $this->assertTrue(in_array('code/c.txt', $this->adapter->files('code')));
        $this->assertTrue(in_array('code/php.txt', $this->adapter->files('code')));
        $this->assertTrue(in_array('code/lang/c.txt', $this->adapter->files('code/lang')));
        $this->assertFalse(in_array('code/javascript.txt', $this->adapter->files('code')));
        $this->assertFalse(in_array('languages/c.txt', $this->adapter->files('languages')));
        $this->assertFalse(in_array('languages/php.txt', $this->adapter->files('languages')));
        $this->assertFalse(in_array('languages/lang/c.txt', $this->adapter->files('languages/lang')));
    }

    public function testUrlLocal()
    {
        $connector = new LocalConnector();

        $adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]), []);

        $adapter->write('url.txt', 'php');

        $this->assertSame(
            self::normalizeDirectorySeparator($this->root . '/url.txt'),
            self::normalizeDirectorySeparator($adapter->url('url.txt'))
        );

        $adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]), ['url' => 'test']);

        $this->assertSame(
            self::normalizeDirectorySeparator('test/url.txt'),
            self::normalizeDirectorySeparator($adapter->url('url.txt'))
        );
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
