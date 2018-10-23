<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Util;
use org\bovigo\vfs\content\LargeFileContent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException;
use Viserio\Component\Filesystem\Adapter\LocalConnector;
use Viserio\Component\Filesystem\FilesystemAdapter;

/**
 * @internal
 */
final class FilesystemAdapterTest extends TestCase
{
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
        $this->root = __DIR__ . \DIRECTORY_SEPARATOR . 'FileCache';

        @\mkdir($this->root);

        $connector = new LocalConnector(['path' => $this->root]);

        $this->adapter = new FilesystemAdapter($connector->connect(), []);
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
        $this->adapter->createDirectory('test-dir');

        static::assertTrue($this->adapter->isDirectory('test-dir'));
    }

    public function testReadRetrievesFiles(): void
    {
        $this->adapter->write('test.txt', 'Hello World');

        static::assertEquals('Hello World', $this->adapter->read('test.txt'));
    }

    public function testPutStoresFiles(): void
    {
        $this->adapter->put('test.txt', 'Hello World');

        static::assertEquals('Hello World', $this->adapter->read('test.txt'));

        $this->adapter->put('test.txt', 'Hello World 2');

        static::assertEquals('Hello World 2', $this->adapter->read('test.txt'));
    }

    public function testReadToThrowException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->adapter->read('test2.txt');
    }

    public function testReadStreamToThrowException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->adapter->readStream('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'file.php');
    }

    public function testUpdateStoresFiles(): void
    {
        $this->adapter->write('test.txt', 'test');
        $this->adapter->update('test.txt', 'Hello World');

        static::assertEquals('Hello World', $this->adapter->read('test.txt'));
    }

    public function testUpdateToThrowException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->adapter->update($this->root . 'TestDontExists.txt', 'Hello World');
    }

    public function testUpdateStream(): void
    {
        $temp = \tmpfile();

        \fwrite($temp, 'copy');
        \rewind($temp);

        $this->adapter->writeStream('stream.txt', $temp);

        \fwrite($temp, 'dummy');
        \rewind($temp);

        static::assertTrue($this->adapter->updateStream('stream.txt', $temp, ['visibility' => 'public']));

        $stream = $this->adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);
        \fclose($temp);

        static::assertSame(9, $size);
        static::assertSame('copydummy', $contents);
    }

    public function testDeleteDirectory(): void
    {
        $this->adapter->createDirectory('delete-dir');
        $this->adapter->write(\DIRECTORY_SEPARATOR . 'delete-dir' . \DIRECTORY_SEPARATOR . 'delete.txt', 'delete');

        static::assertDirectoryExists($this->root . \DIRECTORY_SEPARATOR . 'delete-dir');
        static::assertFalse($this->adapter->deleteDirectory($this->root . \DIRECTORY_SEPARATOR . 'delete-dir' . \DIRECTORY_SEPARATOR . 'delete.txt'));

        $this->adapter->deleteDirectory('delete-dir');

        static::assertDirectoryNotExists($this->root . \DIRECTORY_SEPARATOR . 'delete-dir');
        static::assertFileNotExists($this->root . \DIRECTORY_SEPARATOR . 'delete-dir' . \DIRECTORY_SEPARATOR . 'delete.txt');
    }

    public function testCleanDirectory(): void
    {
        $this->adapter->createDirectory('tempdir');
        $this->adapter->write('tempdir' . \DIRECTORY_SEPARATOR . 'tempfoo.txt', 'tempfoo');

        static::assertFalse($this->adapter->cleanDirectory('tempdir' . \DIRECTORY_SEPARATOR . 'tempfoo.txt'));

        $this->adapter->cleanDirectory('tempdir');

        static::assertDirectoryExists($this->root . \DIRECTORY_SEPARATOR . 'tempdir');
        static::assertFileNotExists($this->root . \DIRECTORY_SEPARATOR . 'tempfoo.txt');
    }

    public function testDeleteRemovesFiles(): void
    {
        $this->adapter->write('unlucky.txt', 'delete');

        static::assertTrue($this->adapter->has('unlucky.txt'));

        $this->adapter->delete(['unlucky.txt']);

        static::assertFalse($this->adapter->has('unlucky.txt'));
    }

    public function testMoveMovesFiles(): void
    {
        $this->adapter->write('pop.txt', 'delete');

        $this->adapter->move('pop.txt', 'rock.txt');

        static::assertFileExists($this->root . \DIRECTORY_SEPARATOR . 'rock.txt');
        static::assertStringEqualsFile($this->root . \DIRECTORY_SEPARATOR . 'rock.txt', 'delete');
        static::assertFileNotExists('pop.txt');
    }

    public function testGetMimeTypeOutputsMimeType(): void
    {
        $this->adapter->write('foo.txt', 'test');

        static::assertEquals('text/plain', $this->adapter->getMimetype('foo.txt'));
    }

    public function testGetSizeOutputsSize(): void
    {
        $content = LargeFileContent::withKilobytes(2);
        $this->adapter->write('2kb.txt', $content->content());

        static::assertEquals(\filesize($this->root . \DIRECTORY_SEPARATOR . '2kb.txt'), $this->adapter->getSize('2kb.txt'));
    }

    public function testAllFilesFindsFiles(): void
    {
        $this->adapter->createDirectory('languages');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'php.txt', 'php');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'c.txt', 'c');

        $allFiles = $this->adapter->allFiles('languages');

        static::assertContains('languages/c.txt', $allFiles);
        static::assertContains('languages/php.txt', $allFiles);
    }

    public function testDirectoriesFindsDirectories(): void
    {
        $this->adapter->createDirectory('test');
        $this->adapter->createDirectory('test' . \DIRECTORY_SEPARATOR . 'languages');
        $this->adapter->createDirectory('test' . \DIRECTORY_SEPARATOR . 'music');

        $directories = $this->adapter->directories('test');

        static::assertContains('test/languages', $directories);
        static::assertContains('test/music', $directories);
    }

    public function testCreateDirectory(): void
    {
        $this->adapter->createDirectory('test-dir');

        static::assertSame('public', $this->adapter->getVisibility('test-dir'));
    }

    public function testCopy(): void
    {
        $this->adapter->write('file.ext', 'content', ['visibility' => 'public']);

        static::assertTrue($this->adapter->copy('file.ext', 'new.ext'));
        static::assertTrue($this->adapter->has('new.ext'));
    }

    public function testCopyToThrowIOException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\IOException::class);

        $this->adapter->write('file.ext', 'content', ['visibility' => 'private']);

        $this->adapter->copy('file.ext', \DIRECTORY_SEPARATOR . 'test' . \DIRECTORY_SEPARATOR);
    }

    public function testCopyToThrowFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->adapter->copy('notexist.test', 'copy');
    }

    public function testGetAndSetVisibility(): void
    {
        $this->adapter->write('copy.txt', 'content');

        static::assertSame('public', $this->adapter->getVisibility('copy.txt'));

        $this->adapter->setVisibility('copy.txt', 'public');

        static::assertSame('public', $this->adapter->getVisibility('copy.txt'));
    }

    public function testSetVisibilityToThrowInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->adapter->createDirectory('test-dir');
        $this->adapter->setVisibility('test-dir', 'exception');
    }

    public function testGetMimetype(): void
    {
        $this->adapter->write('text.txt', 'contents', []);

        static::assertEquals('text/plain', $this->adapter->getMimetype('text.txt'));
    }

    public function testGetMimeTypeToThrowFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->adapter->getMimetype($this->root . \DIRECTORY_SEPARATOR . 'DontExist');
    }

    public function testGetTimestamp(): void
    {
        $this->adapter->write('dummy.txt', '1234');

        static::assertInternalType('int', $this->adapter->getTimestamp('dummy.txt'));
    }

    public function testGetTimestampToThrowFileNotFoundException(): void
    {
        $this->expectException(FileNotFoundException::class);

        $this->adapter->getTimestamp(\DIRECTORY_SEPARATOR . 'DontExist');
    }

    public function testFiles(): void
    {
        $this->adapter->createDirectory('languages');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'php.txt', 'php');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'c.txt', 'c');
        $this->adapter->createDirectory('languages' . \DIRECTORY_SEPARATOR . 'lang');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'lang' . \DIRECTORY_SEPARATOR . 'c.txt', 'c');

        static::assertContains('languages/c.txt', $this->adapter->files('languages'));
        static::assertContains('languages/php.txt', $this->adapter->files('languages'));
        static::assertNotContains('languages/lang/c.txt', $this->adapter->files('languages'));
    }

    public function testCopyDirectoryMovesEntireDirectory(): void
    {
        $this->adapter->createDirectory('languages');
        $this->adapter->createDirectory('root');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'php.txt', 'php');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'c.txt', 'c');
        $this->adapter->createDirectory('languages' . \DIRECTORY_SEPARATOR . 'lang');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'lang' . \DIRECTORY_SEPARATOR . 'c.txt', 'c');

        $this->adapter->copyDirectory('languages', 'root');

        static::assertFalse($this->adapter->copyDirectory('dontmove', 'code'));
        static::assertSame($this->adapter->getVisibility('languages'), $this->adapter->getVisibility('root'));
        static::assertContains('root/c.txt', $this->adapter->files('root'));
        static::assertContains('root/php.txt', $this->adapter->files('root'));
        static::assertContains('root/lang/c.txt', $this->adapter->files('root' . \DIRECTORY_SEPARATOR . 'lang'));
    }

    public function testMoveDirectoryMovesEntireDirectory(): void
    {
        $this->adapter->createDirectory('languages');
        $this->adapter->createDirectory('root');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'php.txt', 'php');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'c.txt', 'c');
        $this->adapter->createDirectory('languages' . \DIRECTORY_SEPARATOR . 'lang');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'lang' . \DIRECTORY_SEPARATOR . 'c.txt', 'c');

        $this->adapter->moveDirectory('languages', 'root');

        static::assertContains('root/c.txt', $this->adapter->files('root'));
        static::assertContains('root/php.txt', $this->adapter->files('root'));
        static::assertContains('root/lang/c.txt', $this->adapter->files('root' . \DIRECTORY_SEPARATOR . 'lang'));
        static::assertNotContains('languages/c.txt', $this->adapter->files('languages'));
        static::assertNotContains('languages/php.txt', $this->adapter->files('languages'));
        static::assertNotContains('languages/lang/c.txt', $this->adapter->files('languages' . \DIRECTORY_SEPARATOR . 'lang'));
    }

    public function testMoveDirectoryMovesEntireDirectoryAndOverwrites(): void
    {
        $this->adapter->createDirectory('languages');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'php.txt', 'php');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'c.txt', 'c');
        $this->adapter->createDirectory('languages' . \DIRECTORY_SEPARATOR . 'lang');
        $this->adapter->write('languages' . \DIRECTORY_SEPARATOR . 'lang' . \DIRECTORY_SEPARATOR . 'c.txt', 'c');

        $this->adapter->createDirectory('code');
        $this->adapter->write('code' . \DIRECTORY_SEPARATOR . 'javascript.txt', 'javascript');

        $this->adapter->moveDirectory('languages', 'code', ['overwrite' => true]);

        static::assertTrue($this->adapter->isWritable('code'));
        static::assertContains('code/c.txt', $this->adapter->files('code'));
        static::assertContains('code/php.txt', $this->adapter->files('code'));
        static::assertContains('code/lang/c.txt', $this->adapter->files('code' . \DIRECTORY_SEPARATOR . 'lang'));
        static::assertNotContains('code/javascript.txt', $this->adapter->files('code'));
        static::assertNotContains('languages/c.txt', $this->adapter->files('languages'));
        static::assertNotContains('languages/php.txt', $this->adapter->files('languages'));
        static::assertNotContains('languages/lang/c.txt', $this->adapter->files('languages' . \DIRECTORY_SEPARATOR . 'lang'));
    }

    public function testUrlLocal(): void
    {
        $connector = new LocalConnector(['path' => $this->root]);
        $adapter   = new FilesystemAdapter($connector->connect(), []);

        $adapter->write('url.txt', 'php');

        static::assertSame(
            $this->root . \DIRECTORY_SEPARATOR . 'url.txt',
            $adapter->url('url.txt')
        );

        $connector = new LocalConnector(['path' => $this->root]);
        $adapter   = new FilesystemAdapter($connector->connect(), ['url' => 'test']);

        static::assertSame(
            'test' . \DIRECTORY_SEPARATOR . 'url.txt',
            $adapter->url('url.txt')
        );
    }

    public function testAppendOnExistingFile(): void
    {
        $url = 'append.txt';

        $this->adapter->write($url, 'Foo Bar');

        static::assertTrue($this->adapter->append($url, ' test'));
        static::assertEquals('Foo Bar test', $this->adapter->read($url));
    }

    public function testAppend(): void
    {
        static::assertTrue($this->adapter->append('append.txt', 'test'));
        static::assertEquals('test', $this->adapter->read('append.txt'));
    }

    public function testAppendStreamOnExistingFile(): void
    {
        $temp = \tmpfile();

        \fwrite($temp, 'copy');
        \rewind($temp);

        $this->adapter->writeStream('stream.txt', $temp);

        \fwrite($temp, ' dummy');
        \rewind($temp);

        static::assertTrue($this->adapter->appendStream('stream.txt', $temp));

        $stream = $this->adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);

        static::assertSame(10, $size);
        static::assertSame('copy dummy', $contents);
        static::assertInternalType('resource', $stream);
    }

    public function testAppendStream(): void
    {
        $temp = \tmpfile();

        \fwrite($temp, ' dummy');
        \rewind($temp);

        static::assertTrue($this->adapter->appendStream('stream.txt', $temp));

        $stream = $this->adapter->readStream('stream.txt');

        $contents = \stream_get_contents($stream);
        $size     = Util::getStreamSize($stream);

        \fclose($stream);

        static::assertSame(6, $size);
        static::assertSame(' dummy', $contents);
        static::assertInternalType('resource', $stream);
    }
}
