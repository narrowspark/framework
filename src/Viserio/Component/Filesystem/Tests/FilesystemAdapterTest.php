<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Util;
use org\bovigo\vfs\content\LargeFileContent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Filesystem\Adapter\LocalConnector;
use Viserio\Component\Filesystem\FilesystemAdapter;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class FilesystemAdapterTest extends TestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var string
     */
    private $root;

    /**
     * @var \Viserio\Component\Filesystem\FilesystemAdapter
     */
    private $adapter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = self::normalizeDirectorySeparator(__DIR__ . '/FileCache');

        @\mkdir($this->root);

        $connector = new LocalConnector();

        $this->adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]), []);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->root);
    }

    public function testGetDriver(): void
    {
        $this->assertInstanceOf(AdapterInterface::class, $this->adapter->getDriver());
    }

    public function testIsDir(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');

        $this->assertTrue($adapter->isDirectory('test-dir'));
    }

    public function testReadRetrievesFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('test.txt', 'Hello World');

        $this->assertEquals('Hello World', $adapter->read('test.txt'));
    }

    public function testPutStoresFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->put('test.txt', 'Hello World');

        $this->assertEquals('Hello World', $adapter->read('test.txt'));

        $adapter->put('test.txt', 'Hello World 2');

        $this->assertEquals('Hello World 2', $adapter->read('test.txt'));
    }

    public function testReadToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException::class);

        $this->adapter->read('test2.txt');
    }

    public function testReadStreamToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException::class);

        $this->adapter->readStream('foo/bar/tmp/file.php');
    }

    public function testUpdateStoresFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('test.txt', 'test');
        $adapter->update('test.txt', 'Hello World');

        $this->assertEquals('Hello World', $adapter->read('test.txt'));
    }

    public function testUpdateToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException::class);

        $this->adapter->update($this->root . 'TestDontExists.txt', 'Hello World');
    }

    public function testUpdateStream(): void
    {
        $adapter = $this->adapter;

        $temp = \tmpfile();

        \fwrite($temp, 'copy');
        \rewind($temp);

        $adapter->writeStream('stream.txt', $temp);

        \fwrite($temp, 'dummy');
        \rewind($temp);

        $this->assertTrue($adapter->updateStream('stream.txt', $temp, ['visibility' => 'public']));

        $stream = $adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);
        \fclose($temp);

        $this->assertSame(9, $size);
        $this->assertSame('copydummy', $contents);
    }

    public function testDeleteDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('delete-dir');
        $adapter->write('/delete-dir/delete.txt', 'delete');

        $this->assertTrue(\is_dir($this->root . '/delete-dir'));
        $this->assertFalse($adapter->deleteDirectory($this->root . '/delete-dir/delete.txt'));

        $adapter->deleteDirectory('delete-dir');

        $this->assertFalse(\is_dir($this->root . '/delete-dir'));
        $this->assertFileNotExists($this->root . '/delete-dir/delete.txt');
    }

    public function testCleanDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('tempdir');
        $adapter->write('tempdir/tempfoo.txt', 'tempfoo');

        $this->assertFalse($adapter->cleanDirectory('tempdir/tempfoo.txt'));

        $adapter->cleanDirectory('tempdir');

        $this->assertTrue(\is_dir($this->root . '/tempdir'));
        $this->assertFileNotExists($this->root . '/tempfoo.txt');
    }

    public function testDeleteRemovesFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('unlucky.txt', 'delete');

        $this->assertTrue($adapter->has('unlucky.txt'));

        $adapter->delete(['unlucky.txt']);

        $this->assertFalse($adapter->has('unlucky.txt'));
    }

    public function testMoveMovesFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('pop.txt', 'delete');

        $adapter->move('pop.txt', 'rock.txt');

        $this->assertFileExists($this->root . '/rock.txt');
        $this->assertStringEqualsFile($this->root . '/rock.txt', 'delete');
        $this->assertFileNotExists('pop.txt');
    }

    public function testGetMimeTypeOutputsMimeType(): void
    {
        $adapter = $this->adapter;

        $adapter->write('foo.txt', 'test');

        $this->assertEquals('text/plain', $adapter->getMimetype('foo.txt'));
    }

    public function testGetSizeOutputsSize(): void
    {
        $content = LargeFileContent::withKilobytes(2);
        $adapter = $this->adapter;

        $adapter->write('2kb.txt', $content->content());

        $this->assertEquals(\filesize($this->root . '/2kb.txt'), $adapter->getSize('2kb.txt'));
    }

    public function testAllFilesFindsFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');

        $allFiles = $this->adapter->allFiles('languages');

        $this->assertTrue(\in_array('languages/c.txt', $allFiles, true));
        $this->assertTrue(\in_array('languages/php.txt', $allFiles, true));
    }

    public function testDirectoriesFindsDirectories(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test');
        $adapter->createDirectory('test/languages');
        $adapter->createDirectory('test/music');

        $directories = $adapter->directories('test');

        $this->assertTrue(\in_array('test/languages', $directories, true));
        $this->assertTrue(\in_array('test/music', $directories, true));
    }

    public function testCreateDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');

        $this->assertSame('public', $adapter->getVisibility('test-dir'));
    }

    public function testCopy(): void
    {
        $adapter = $this->adapter;

        $adapter->write('file.ext', 'content', ['visibility' => 'public']);

        $this->assertTrue($adapter->copy('file.ext', 'new.ext'));
        $this->assertTrue($adapter->has('new.ext'));
    }

    public function testCopyToThrowIOException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\IOException::class);

        $adapter = $this->adapter;

        $adapter->write('file.ext', 'content', ['visibility' => 'private']);

        $adapter->copy('file.ext', '/test/');
    }

    public function testCopyToThrowFileNotFoundException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException::class);

        $adapter = $this->adapter;

        $adapter->copy('notexist.test', 'copy');
    }

    public function testGetAndSetVisibility(): void
    {
        $adapter = $this->adapter;

        $adapter->write('copy.txt', 'content');

        $this->assertSame('public', $this->adapter->getVisibility('copy.txt'));

        $this->adapter->setVisibility('copy.txt', 'public');

        $this->assertSame('public', $this->adapter->getVisibility('copy.txt'));
    }

    public function testSetVisibilityToThrowInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');
        $adapter->setVisibility('test-dir', 'exception');
    }

    public function testGetMimetype(): void
    {
        $this->adapter->write('text.txt', 'contents', []);

        $this->assertEquals('text/plain', $this->adapter->getMimetype('text.txt'));
    }

    public function testGetMimetypeToThrowFileNotFoundException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException::class);

        $this->adapter->getMimetype($this->root . '/DontExist');
    }

    public function testGetTimestamp(): void
    {
        $adapter = $this->adapter;

        $adapter->write('dummy.txt', '1234');

        $this->assertInternalType('int', $adapter->getTimestamp('dummy.txt'));
    }

    public function testGetTimestampToThrowFileNotFoundException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException::class);

        $this->adapter->getTimestamp('/DontExist');
    }

    public function testFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');
        $adapter->createDirectory('languages/lang');
        $adapter->write('languages/lang/c.txt', 'c');

        $this->assertTrue(\in_array('languages/c.txt', $this->adapter->files('languages'), true));
        $this->assertTrue(\in_array('languages/php.txt', $this->adapter->files('languages'), true));
        $this->assertFalse(\in_array('languages/lang/c.txt', $this->adapter->files('languages'), true));
    }

    public function testCopyDirectoryMovesEntireDirectory(): void
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
        $this->assertTrue(\in_array('root/c.txt', $this->adapter->files('root'), true));
        $this->assertTrue(\in_array('root/php.txt', $this->adapter->files('root'), true));
        $this->assertTrue(\in_array('root/lang/c.txt', $this->adapter->files('root/lang'), true));
    }

    public function testMoveDirectoryMovesEntireDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->createDirectory('root');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');
        $adapter->createDirectory('languages/lang');
        $adapter->write('languages/lang/c.txt', 'c');

        $this->adapter->moveDirectory('languages', 'root');

        $this->assertTrue(\in_array('root/c.txt', $this->adapter->files('root'), true));
        $this->assertTrue(\in_array('root/php.txt', $this->adapter->files('root'), true));
        $this->assertTrue(\in_array('root/lang/c.txt', $this->adapter->files('root/lang'), true));
        $this->assertFalse(\in_array('languages/c.txt', $this->adapter->files('languages'), true));
        $this->assertFalse(\in_array('languages/php.txt', $this->adapter->files('languages'), true));
        $this->assertFalse(\in_array('languages/lang/c.txt', $this->adapter->files('languages/lang'), true));
    }

    public function testMoveDirectoryMovesEntireDirectoryAndOverwrites(): void
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
        $this->assertTrue(\in_array('code/c.txt', $this->adapter->files('code'), true));
        $this->assertTrue(\in_array('code/php.txt', $this->adapter->files('code'), true));
        $this->assertTrue(\in_array('code/lang/c.txt', $this->adapter->files('code/lang'), true));
        $this->assertFalse(\in_array('code/javascript.txt', $this->adapter->files('code'), true));
        $this->assertFalse(\in_array('languages/c.txt', $this->adapter->files('languages'), true));
        $this->assertFalse(\in_array('languages/php.txt', $this->adapter->files('languages'), true));
        $this->assertFalse(\in_array('languages/lang/c.txt', $this->adapter->files('languages/lang'), true));
    }

    public function testUrlLocal(): void
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

    public function testAppendOnExistingFile(): void
    {
        $adapter = $this->adapter;
        $url     = 'append.txt';

        $adapter->write($url, 'Foo Bar');
        $this->assertTrue($adapter->append($url, ' test'));

        $this->assertEquals('Foo Bar test', $adapter->read($url));
    }

    public function testAppend(): void
    {
        $adapter = $this->adapter;

        $this->assertTrue($adapter->append('append.txt', 'test'));

        $this->assertEquals('test', $adapter->read('append.txt'));
    }

    public function testAppendStreamOnExistingFile(): void
    {
        $adapter = $this->adapter;

        $temp = \tmpfile();

        \fwrite($temp, 'copy');
        \rewind($temp);

        $adapter->writeStream('stream.txt', $temp);

        \fwrite($temp, ' dummy');
        \rewind($temp);

        $this->assertTrue($adapter->appendStream('stream.txt', $temp));

        $stream = $adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);

        $this->assertSame(10, $size);
        $this->assertSame('copy dummy', $contents);
        $this->assertInternalType('resource', $stream);
    }

    public function testAppendStream(): void
    {
        $adapter = $this->adapter;

        $temp = \tmpfile();

        \fwrite($temp, ' dummy');
        \rewind($temp);

        $this->assertTrue($adapter->appendStream('stream.txt', $temp));

        $stream = $adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);

        $this->assertSame(6, $size);
        $this->assertSame(' dummy', $contents);
        $this->assertInternalType('resource', $stream);
    }
}
