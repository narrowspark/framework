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

class FilesystemAdapterTest extends TestCase
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
    public function setUp(): void
    {
        $this->root = self::normalizeDirectorySeparator(__DIR__ . '/FileCache');

        @\mkdir($this->root);

        $connector = new LocalConnector();

        $this->adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]), []);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove($this->root);
    }

    public function testGetDriver(): void
    {
        self::assertInstanceOf(AdapterInterface::class, $this->adapter->getDriver());
    }

    public function testIsDir(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');

        self::assertTrue($adapter->isDirectory('test-dir'));
    }

    public function testReadRetrievesFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('test.txt', 'Hello World');

        self::assertEquals('Hello World', $adapter->read('test.txt'));
    }

    public function testPutStoresFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->put('test.txt', 'Hello World');

        self::assertEquals('Hello World', $adapter->read('test.txt'));

        $adapter->put('test.txt', 'Hello World 2');

        self::assertEquals('Hello World 2', $adapter->read('test.txt'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     */
    public function testReadToThrowException(): void
    {
        $this->adapter->read('test2.txt');
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     */
    public function testReadStreamToThrowException(): void
    {
        $this->adapter->readStream('foo/bar/tmp/file.php');
    }

    public function testUpdateStoresFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('test.txt', 'test');
        $adapter->update('test.txt', 'Hello World');

        self::assertEquals('Hello World', $adapter->read('test.txt'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     */
    public function testUpdateToThrowException(): void
    {
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

        self::assertTrue($adapter->updateStream('stream.txt', $temp, ['visibility' => 'public']));

        $stream = $adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);
        \fclose($temp);

        self::assertSame(9, $size);
        self::assertSame('copydummy', $contents);
    }

    public function testDeleteDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('delete-dir');
        $adapter->write('/delete-dir/delete.txt', 'delete');

        self::assertTrue(\is_dir($this->root . '/delete-dir'));
        self::assertFalse($adapter->deleteDirectory($this->root . '/delete-dir/delete.txt'));

        $adapter->deleteDirectory('delete-dir');

        self::assertFalse(\is_dir($this->root . '/delete-dir'));
        self::assertFileNotExists($this->root . '/delete-dir/delete.txt');
    }

    public function testCleanDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('tempdir');
        $adapter->write('tempdir/tempfoo.txt', 'tempfoo');

        self::assertFalse($adapter->cleanDirectory('tempdir/tempfoo.txt'));

        $adapter->cleanDirectory('tempdir');

        self::assertTrue(\is_dir($this->root . '/tempdir'));
        self::assertFileNotExists($this->root . '/tempfoo.txt');
    }

    public function testDeleteRemovesFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('unlucky.txt', 'delete');

        self::assertTrue($adapter->has('unlucky.txt'));

        $adapter->delete(['unlucky.txt']);

        self::assertFalse($adapter->has('unlucky.txt'));
    }

    public function testMoveMovesFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('pop.txt', 'delete');

        $adapter->move('pop.txt', 'rock.txt');

        self::assertFileExists($this->root . '/rock.txt');
        self::assertStringEqualsFile($this->root . '/rock.txt', 'delete');
        self::assertFileNotExists('pop.txt');
    }

    public function testGetMimeTypeOutputsMimeType(): void
    {
        $adapter = $this->adapter;

        $adapter->write('foo.txt', 'test');

        self::assertEquals('text/plain', $adapter->getMimetype('foo.txt'));
    }

    public function testGetSizeOutputsSize(): void
    {
        $content = LargeFileContent::withKilobytes(2);
        $adapter = $this->adapter;

        $adapter->write('2kb.txt', $content->content());

        self::assertEquals(\filesize($this->root . '/2kb.txt'), $adapter->getSize('2kb.txt'));
    }

    public function testAllFilesFindsFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');

        $allFiles = $this->adapter->allFiles('languages');

        self::assertTrue(\in_array('languages/c.txt', $allFiles, true));
        self::assertTrue(\in_array('languages/php.txt', $allFiles, true));
    }

    public function testDirectoriesFindsDirectories(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test');
        $adapter->createDirectory('test/languages');
        $adapter->createDirectory('test/music');

        $directories = $adapter->directories('test');

        self::assertTrue(\in_array('test/languages', $directories, true));
        self::assertTrue(\in_array('test/music', $directories, true));
    }

    public function testCreateDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');

        self::assertSame('public', $adapter->getVisibility('test-dir'));
    }

    public function testCopy(): void
    {
        $adapter = $this->adapter;

        $adapter->write('file.ext', 'content', ['visibility' => 'public']);

        self::assertTrue($adapter->copy('file.ext', 'new.ext'));
        self::assertTrue($adapter->has('new.ext'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\IOException
     */
    public function testCopyToThrowIOException(): void
    {
        $adapter = $this->adapter;

        $adapter->write('file.ext', 'content', ['visibility' => 'private']);

        $adapter->copy('file.ext', '/test/');
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     */
    public function testCopyToThrowFileNotFoundException(): void
    {
        $adapter = $this->adapter;

        $adapter->copy('notexist.test', 'copy');
    }

    public function testGetAndSetVisibility(): void
    {
        $adapter = $this->adapter;

        $adapter->write('copy.txt', 'content');

        self::assertSame('public', $this->adapter->getVisibility('copy.txt'));

        $this->adapter->setVisibility('copy.txt', 'public');

        self::assertSame('public', $this->adapter->getVisibility('copy.txt'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetVisibilityToThrowInvalidArgumentException(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');
        $adapter->setVisibility('test-dir', 'exception');
    }

    public function testGetMimetype(): void
    {
        $this->adapter->write('text.txt', 'contents', []);

        self::assertEquals('text/plain', $this->adapter->getMimetype('text.txt'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     */
    public function testGetMimetypeToThrowFileNotFoundException(): void
    {
        $this->adapter->getMimetype($this->root . '/DontExist');
    }

    public function testGetTimestamp(): void
    {
        $adapter = $this->adapter;

        $adapter->write('dummy.txt', '1234');

        self::assertInternalType('int', $adapter->getTimestamp('dummy.txt'));
    }

    /**
     * @expectedException \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     */
    public function testGetTimestampToThrowFileNotFoundException(): void
    {
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

        self::assertTrue(\in_array('languages/c.txt', $this->adapter->files('languages'), true));
        self::assertTrue(\in_array('languages/php.txt', $this->adapter->files('languages'), true));
        self::assertFalse(\in_array('languages/lang/c.txt', $this->adapter->files('languages'), true));
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

        self::assertFalse($this->adapter->copyDirectory('dontmove', 'code'));
        self::assertSame($this->adapter->getVisibility('languages'), $this->adapter->getVisibility('root'));
        self::assertTrue(\in_array('root/c.txt', $this->adapter->files('root'), true));
        self::assertTrue(\in_array('root/php.txt', $this->adapter->files('root'), true));
        self::assertTrue(\in_array('root/lang/c.txt', $this->adapter->files('root/lang'), true));
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

        self::assertTrue(\in_array('root/c.txt', $this->adapter->files('root'), true));
        self::assertTrue(\in_array('root/php.txt', $this->adapter->files('root'), true));
        self::assertTrue(\in_array('root/lang/c.txt', $this->adapter->files('root/lang'), true));
        self::assertFalse(\in_array('languages/c.txt', $this->adapter->files('languages'), true));
        self::assertFalse(\in_array('languages/php.txt', $this->adapter->files('languages'), true));
        self::assertFalse(\in_array('languages/lang/c.txt', $this->adapter->files('languages/lang'), true));
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

        self::assertTrue($this->adapter->isWritable('code'));
        self::assertTrue(\in_array('code/c.txt', $this->adapter->files('code'), true));
        self::assertTrue(\in_array('code/php.txt', $this->adapter->files('code'), true));
        self::assertTrue(\in_array('code/lang/c.txt', $this->adapter->files('code/lang'), true));
        self::assertFalse(\in_array('code/javascript.txt', $this->adapter->files('code'), true));
        self::assertFalse(\in_array('languages/c.txt', $this->adapter->files('languages'), true));
        self::assertFalse(\in_array('languages/php.txt', $this->adapter->files('languages'), true));
        self::assertFalse(\in_array('languages/lang/c.txt', $this->adapter->files('languages/lang'), true));
    }

    public function testUrlLocal(): void
    {
        $connector = new LocalConnector();

        $adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]), []);

        $adapter->write('url.txt', 'php');

        self::assertSame(
            self::normalizeDirectorySeparator($this->root . '/url.txt'),
            self::normalizeDirectorySeparator($adapter->url('url.txt'))
        );

        $adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]), ['url' => 'test']);

        self::assertSame(
            self::normalizeDirectorySeparator('test/url.txt'),
            self::normalizeDirectorySeparator($adapter->url('url.txt'))
        );
    }

    public function testAppendOnExistingFile(): void
    {
        $adapter = $this->adapter;
        $url     = 'append.txt';

        $adapter->write($url, 'Foo Bar');
        self::assertTrue($adapter->append($url, ' test'));

        self::assertEquals('Foo Bar test', $adapter->read($url));
    }

    public function testAppend(): void
    {
        $adapter = $this->adapter;

        self::assertTrue($adapter->append('append.txt', 'test'));

        self::assertEquals('test', $adapter->read('append.txt'));
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

        self::assertTrue($adapter->appendStream('stream.txt', $temp));

        $stream = $adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);

        self::assertSame(10, $size);
        self::assertSame('copy dummy', $contents);
        self::assertInternalType('resource', $stream);
    }

    public function testAppendStream(): void
    {
        $adapter = $this->adapter;

        $temp = \tmpfile();

        \fwrite($temp, ' dummy');
        \rewind($temp);

        self::assertTrue($adapter->appendStream('stream.txt', $temp));

        $stream = $adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);

        self::assertSame(6, $size);
        self::assertSame(' dummy', $contents);
        self::assertInternalType('resource', $stream);
    }
}
