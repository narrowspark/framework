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
        static::assertInstanceOf(AdapterInterface::class, $this->adapter->getDriver());
    }

    public function testIsDir(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');

        static::assertTrue($adapter->isDirectory('test-dir'));
    }

    public function testReadRetrievesFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('test.txt', 'Hello World');

        static::assertEquals('Hello World', $adapter->read('test.txt'));
    }

    public function testPutStoresFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->put('test.txt', 'Hello World');

        static::assertEquals('Hello World', $adapter->read('test.txt'));

        $adapter->put('test.txt', 'Hello World 2');

        static::assertEquals('Hello World 2', $adapter->read('test.txt'));
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

        static::assertEquals('Hello World', $adapter->read('test.txt'));
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

        static::assertTrue($adapter->updateStream('stream.txt', $temp, ['visibility' => 'public']));

        $stream = $adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);
        \fclose($temp);

        static::assertSame(9, $size);
        static::assertSame('copydummy', $contents);
    }

    public function testDeleteDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('delete-dir');
        $adapter->write('/delete-dir/delete.txt', 'delete');

        static::assertTrue(\is_dir($this->root . '/delete-dir'));
        static::assertFalse($adapter->deleteDirectory($this->root . '/delete-dir/delete.txt'));

        $adapter->deleteDirectory('delete-dir');

        static::assertFalse(\is_dir($this->root . '/delete-dir'));
        static::assertFileNotExists($this->root . '/delete-dir/delete.txt');
    }

    public function testCleanDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('tempdir');
        $adapter->write('tempdir/tempfoo.txt', 'tempfoo');

        static::assertFalse($adapter->cleanDirectory('tempdir/tempfoo.txt'));

        $adapter->cleanDirectory('tempdir');

        static::assertTrue(\is_dir($this->root . '/tempdir'));
        static::assertFileNotExists($this->root . '/tempfoo.txt');
    }

    public function testDeleteRemovesFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('unlucky.txt', 'delete');

        static::assertTrue($adapter->has('unlucky.txt'));

        $adapter->delete(['unlucky.txt']);

        static::assertFalse($adapter->has('unlucky.txt'));
    }

    public function testMoveMovesFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->write('pop.txt', 'delete');

        $adapter->move('pop.txt', 'rock.txt');

        static::assertFileExists($this->root . '/rock.txt');
        static::assertStringEqualsFile($this->root . '/rock.txt', 'delete');
        static::assertFileNotExists('pop.txt');
    }

    public function testGetMimeTypeOutputsMimeType(): void
    {
        $adapter = $this->adapter;

        $adapter->write('foo.txt', 'test');

        static::assertEquals('text/plain', $adapter->getMimetype('foo.txt'));
    }

    public function testGetSizeOutputsSize(): void
    {
        $content = LargeFileContent::withKilobytes(2);
        $adapter = $this->adapter;

        $adapter->write('2kb.txt', $content->content());

        static::assertEquals(\filesize($this->root . '/2kb.txt'), $adapter->getSize('2kb.txt'));
    }

    public function testAllFilesFindsFiles(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('languages');
        $adapter->write('languages/php.txt', 'php');
        $adapter->write('languages/c.txt', 'c');

        $allFiles = $this->adapter->allFiles('languages');

        static::assertTrue(\in_array('languages/c.txt', $allFiles, true));
        static::assertTrue(\in_array('languages/php.txt', $allFiles, true));
    }

    public function testDirectoriesFindsDirectories(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test');
        $adapter->createDirectory('test/languages');
        $adapter->createDirectory('test/music');

        $directories = $adapter->directories('test');

        static::assertTrue(\in_array('test/languages', $directories, true));
        static::assertTrue(\in_array('test/music', $directories, true));
    }

    public function testCreateDirectory(): void
    {
        $adapter = $this->adapter;

        $adapter->createDirectory('test-dir');

        static::assertSame('public', $adapter->getVisibility('test-dir'));
    }

    public function testCopy(): void
    {
        $adapter = $this->adapter;

        $adapter->write('file.ext', 'content', ['visibility' => 'public']);

        static::assertTrue($adapter->copy('file.ext', 'new.ext'));
        static::assertTrue($adapter->has('new.ext'));
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

        static::assertSame('public', $this->adapter->getVisibility('copy.txt'));

        $this->adapter->setVisibility('copy.txt', 'public');

        static::assertSame('public', $this->adapter->getVisibility('copy.txt'));
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

        static::assertEquals('text/plain', $this->adapter->getMimetype('text.txt'));
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

        static::assertInternalType('int', $adapter->getTimestamp('dummy.txt'));
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

        static::assertTrue(\in_array('languages/c.txt', $this->adapter->files('languages'), true));
        static::assertTrue(\in_array('languages/php.txt', $this->adapter->files('languages'), true));
        static::assertFalse(\in_array('languages/lang/c.txt', $this->adapter->files('languages'), true));
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

        static::assertFalse($this->adapter->copyDirectory('dontmove', 'code'));
        static::assertSame($this->adapter->getVisibility('languages'), $this->adapter->getVisibility('root'));
        static::assertTrue(\in_array('root/c.txt', $this->adapter->files('root'), true));
        static::assertTrue(\in_array('root/php.txt', $this->adapter->files('root'), true));
        static::assertTrue(\in_array('root/lang/c.txt', $this->adapter->files('root/lang'), true));
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

        static::assertTrue(\in_array('root/c.txt', $this->adapter->files('root'), true));
        static::assertTrue(\in_array('root/php.txt', $this->adapter->files('root'), true));
        static::assertTrue(\in_array('root/lang/c.txt', $this->adapter->files('root/lang'), true));
        static::assertFalse(\in_array('languages/c.txt', $this->adapter->files('languages'), true));
        static::assertFalse(\in_array('languages/php.txt', $this->adapter->files('languages'), true));
        static::assertFalse(\in_array('languages/lang/c.txt', $this->adapter->files('languages/lang'), true));
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

        static::assertTrue($this->adapter->isWritable('code'));
        static::assertTrue(\in_array('code/c.txt', $this->adapter->files('code'), true));
        static::assertTrue(\in_array('code/php.txt', $this->adapter->files('code'), true));
        static::assertTrue(\in_array('code/lang/c.txt', $this->adapter->files('code/lang'), true));
        static::assertFalse(\in_array('code/javascript.txt', $this->adapter->files('code'), true));
        static::assertFalse(\in_array('languages/c.txt', $this->adapter->files('languages'), true));
        static::assertFalse(\in_array('languages/php.txt', $this->adapter->files('languages'), true));
        static::assertFalse(\in_array('languages/lang/c.txt', $this->adapter->files('languages/lang'), true));
    }

    public function testUrlLocal(): void
    {
        $connector = new LocalConnector();

        $adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]), []);

        $adapter->write('url.txt', 'php');

        static::assertSame(
            self::normalizeDirectorySeparator($this->root . '/url.txt'),
            self::normalizeDirectorySeparator($adapter->url('url.txt'))
        );

        $adapter = new FilesystemAdapter($connector->connect(['path' => $this->root]), ['url' => 'test']);

        static::assertSame(
            self::normalizeDirectorySeparator('test/url.txt'),
            self::normalizeDirectorySeparator($adapter->url('url.txt'))
        );
    }

    public function testAppendOnExistingFile(): void
    {
        $adapter = $this->adapter;
        $url     = 'append.txt';

        $adapter->write($url, 'Foo Bar');
        static::assertTrue($adapter->append($url, ' test'));

        static::assertEquals('Foo Bar test', $adapter->read($url));
    }

    public function testAppend(): void
    {
        $adapter = $this->adapter;

        static::assertTrue($adapter->append('append.txt', 'test'));

        static::assertEquals('test', $adapter->read('append.txt'));
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

        static::assertTrue($adapter->appendStream('stream.txt', $temp));

        $stream = $adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);

        static::assertSame(10, $size);
        static::assertSame('copy dummy', $contents);
        static::assertInternalType('resource', $stream);
    }

    public function testAppendStream(): void
    {
        $adapter = $this->adapter;

        $temp = \tmpfile();

        \fwrite($temp, ' dummy');
        \rewind($temp);

        static::assertTrue($adapter->appendStream('stream.txt', $temp));

        $stream = $adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);

        static::assertSame(6, $size);
        static::assertSame(' dummy', $contents);
        static::assertInternalType('resource', $stream);
    }
}
