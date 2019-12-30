<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem\Tests;

use DateTime;
use Narrowspark\TestingHelper\Traits\AssertArrayTrait;
use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Filesystem\Permissions;
use Viserio\Contract\Filesystem\Exception\IOException;
use Viserio\Contract\Filesystem\Exception\NotFoundException;
use Viserio\Contract\Filesystem\Exception\NotSupportedException;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contract\Finder\Exception\NotFoundException as FinderNotFoundException;

/**
 * @covers \Viserio\Component\Filesystem\Filesystem
 *
 * @internal
 *
 * @small
 */
abstract class AbstractFilesystemTestCase extends TestCase
{
    use AssertArrayTrait;

    /** @var array<string,string> functionName => reason */
    protected $skippedTests = [];

    /** @var int */
    protected $umask;

    /** @var string[] */
    protected $longPathNamesWindows = [];

    /** @var \Viserio\Contract\Filesystem\Filesystem&\Viserio\Contract\Filesystem\LinkSystem */
    protected $filesystem;

    /** @var string */
    protected $workspace;

    /**
     * Flag for hard links on Windows.
     *
     * @var null|bool
     */
    protected static $linkOnWindows;

    /**
     * Flag for symbolic links on Windows.
     *
     * @var null|bool
     */
    protected static $symlinkOnWindows;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::$linkOnWindows = true;

            /** @var string $originFile */
            $originFile = \tempnam(\sys_get_temp_dir(), 'li');
            /** @var string $targetFile */
            $targetFile = \tempnam(\sys_get_temp_dir(), 'li');

            if (true !== @\link($originFile, $targetFile)) {
                $report = \error_get_last();

                if (\is_array($report) && false !== \strpos($report['message'], 'error code(1314)')) {
                    self::$linkOnWindows = false;
                }
            } else {
                @\unlink($targetFile);
            }

            self::$symlinkOnWindows = true;

            /** @var string $originDir */
            $originDir = \tempnam(\sys_get_temp_dir(), 'sl');
            /** @var string $targetDir */
            $targetDir = \tempnam(\sys_get_temp_dir(), 'sl');

            if (true !== @\symlink($originDir, $targetDir)) {
                $report = \error_get_last();

                if (\is_array($report) && false !== \strpos($report['message'], 'error code(1314)')) {
                    self::$symlinkOnWindows = false;
                }
            } else {
                @\unlink($targetDir);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->umask = \umask(0);

        $this->workspace = \sys_get_temp_dir() . '/' . \microtime(true) . '.' . \mt_rand();

        \mkdir($this->workspace, 0777, true);

        $this->workspace = (string) \realpath($this->workspace);

        $this->filesystem = $this->getFilesystem();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        foreach ($this->longPathNamesWindows as $path) {
            \exec('DEL ' . $path);
        }

        $this->longPathNamesWindows = [];

        try {
            $this->filesystem->deleteDirectory($this->workspace);
        } catch (FinderNotFoundException $exception) {
        }

        \umask($this->umask);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilesystem(): FilesystemContract
    {
        return new Filesystem();
    }

    /**
     * Assert the permission of a file.
     *
     * @param int    $expectedFilePerms Expected file permissions as three digits (i.e. 755)
     * @param string $filePath
     *
     * @return void
     */
    protected function assertFilePermissions($expectedFilePerms, $filePath): void
    {
        $actualFilePerms = (int) \substr(\sprintf('%o', \fileperms($filePath)), -3);

        self::assertEquals(
            $expectedFilePerms,
            $actualFilePerms,
            \sprintf('File permissions for %s must be %s. Actual %s', $filePath, $expectedFilePerms, $actualFilePerms)
        );
    }

    /**
     * Get the owner of a file.
     *
     * @param string $filepath
     *
     * @return string
     */
    protected function getFileOwner(string $filepath): string
    {
        $this->markAsSkippedIfPosixIsMissing();

        $infos = \stat($filepath);

        if ($infos === false) {
            self::markTestSkipped('Unable to retrieve file owner name');
        }

        $data = \posix_getpwuid($infos[4]); // uid

        return $data['name'];
    }

    /**
     * Get the group of a file.
     *
     * @param string $filepath
     *
     * @return string
     */
    protected function getFileGroup(string $filepath): string
    {
        $this->markAsSkippedIfPosixIsMissing();

        $infos = \stat($filepath);

        if ($infos === false) {
            self::markTestSkipped('Unable to retrieve file group name');
        }

        $data = \posix_getgrgid($infos[5]); // gid

        return $data['name'];
    }

    /**
     * Check if link is supported, if not skip the test.
     *
     * @return void
     */
    protected function markAsSkippedIfLinkIsMissing(): void
    {
        if (! \function_exists('link')) {
            self::markTestSkipped('link is not supported');
        }

        if (\PHP_OS_FAMILY === 'Windows' && (self::$linkOnWindows === false)) {
            self::markTestSkipped('link requires "Create hard links" privilege on windows');
        }
    }

    /**
     * Check if symbolic link is supported, if not skip the test.
     *
     * @param bool $relative
     *
     * @return void
     */
    protected function markAsSkippedIfSymlinkIsMissing($relative = false): void
    {
        if (\PHP_OS_FAMILY === 'Windows' && self::$symlinkOnWindows === false) {
            self::markTestSkipped('symlink requires "Create symbolic links" privilege on Windows');
        }

        // https://bugs.php.net/69473
        if ($relative && \PHP_OS_FAMILY === 'Windows' && 1 === \PHP_ZTS) {
            self::markTestSkipped('symlink does not support relative paths on thread safe Windows PHP versions');
        }
    }

    /**
     * Check if chmod is supported, if not skip the test.
     *
     * @return void
     */
    protected function markAsSkippedIfChmodIsMissing(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('chmod is not supported on Windows');
        }
    }

    /**
     * Check if posix_isatty is supported, if not skip the test.
     *
     * @return void
     */
    protected function markAsSkippedIfPosixIsMissing(): void
    {
        if (! \function_exists('posix_isatty')) {
            self::markTestSkipped('Function posix_isatty is required.');
        }
    }

    public function testUpdate(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createFile(__FUNCTION__ . '.txt');

        $this->filesystem->update($file, 'Hello World');
        $this->filesystem->update($file, 'Hello World2');

        self::assertStringEqualsFile($file, 'Hello World2');
    }

    public function testUpdateToThrowException(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);

        $this->filesystem->update('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'file.php', 'Hello World');
    }

    public function testUpdateCanChangeVisibility(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfChmodIsMissing();

        $file = $this->createFile('temp.txt');

        $this->filesystem->update($file, 'Hello World', ['visibility' => 'public']);

        self::assertStringEqualsFile($file, 'Hello World');
        self::assertSame(644, $this->filesystem->getVisibility($file));
    }

    public function testDeleteDirectory(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('temp');
        $file = $this->createFile('bar.txt', 'bar', 'temp');

        self::assertDirectoryExists($dir);

        $this->filesystem->deleteDirectory($dir);

        self::assertDirectoryNotExists($dir);
        self::assertFileNotExists($file);
    }

    public function testCleanDirectory(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->createDir('root');

        $dir = $this->createDir('tmpdir', 'root');
        $dir2 = $this->createDir('test', 'tmpdir');

        $file = $this->createFile('tempfoo.txt', 'tempfoo', 'tmpdir');

        $this->filesystem->cleanDirectory($dir);

        self::assertDirectoryExists($dir);
        self::assertDirectoryNotExists($dir2);
        self::assertFileNotExists($file);
    }

    public function testDeleteRemovesFiles(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createFile('unlucky.txt', 'So sad');

        self::assertTrue($this->filesystem->has($file));

        $this->filesystem->delete($file);

        self::assertFalse($this->filesystem->has($file));
    }

    public function testMoveMovesFiles(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('root');
        $file = $this->createFile('pop.txt', 'pop');

        $rock = $dir . \DIRECTORY_SEPARATOR . 'rock.txt';

        $this->filesystem->move($file, $rock);

        self::assertFileExists($rock);
        self::assertStringEqualsFile($rock, 'pop');
        self::assertFileNotExists($dir . \DIRECTORY_SEPARATOR . 'pop.txt');
    }

    public function testUpdateStream(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->createDir('copy');

        $file = $this->createFile('copy.txt', 'copy', 'copy');

        self::assertSame('copy', $this->filesystem->read($file));

        /** @var resource $temp */
        $temp = \tmpfile();

        \fwrite($temp, 'dummy');
        \rewind($temp);

        $this->filesystem->updateStream($file, $temp, ['visibility' => 'public']);

        foreach ($this->filesystem->readStream($file) as $item) {
            self::assertSame(5, \strlen($item));
            self::assertSame('dummy', $item);
        }
    }

    public function testGetSizeOutputsSize(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createFile('2kb.txt', $this->createFileContent(2));

        self::assertEquals(2048, $this->filesystem->getSize($file));
    }

    public function testGetSizeThrowsNotFoundException(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);

        $this->filesystem->getSize('');
    }

    public function testIsDirectory(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('assets');
        $file = $this->createFile('assetsFile');

        self::assertTrue($this->filesystem->isDirectory($dir));
        self::assertFalse($this->filesystem->isDirectory($file));
    }

    public function testAllFilesFindsFiles(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('languages');

        $file1 = $this->createFile('php.txt', 'PHP', 'languages');
        $file2 = $this->createFile('c.txt', 'C', 'languages');

        $allFiles = \iterator_to_array($this->filesystem->allFiles($dir));

        self::assertInArray($file1, $allFiles);
        self::assertInArray($file2, $allFiles);

        self::assertSame([], \iterator_to_array($this->filesystem->allFiles('noop')));
    }

    public function testDirectoriesFindsDirectories(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('root');
        $dir2 = $this->createDir('languages');
        $dir3 = $this->createDir('music');

        $directories = \iterator_to_array($this->filesystem->directories($dir));

        self::assertInArray($dir2, $directories);
        self::assertInArray($dir3, $directories);

        self::assertSame([], \iterator_to_array($this->filesystem->directories('noop')));
    }

    public function testFiles(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->createDir('root');

        $dir = $this->createDir('tmp');

        $file = $this->createFile('foo.txt', 'foo', 'tmp');
        $file2 = $this->createFile('bar.txt', 'bar', 'tmp');

        $this->createDir('nested', 'tmp');

        $file3 = $this->createFile('baz.txt', 'baz', 'tmp.nested');

        $files = \iterator_to_array($this->filesystem->files($dir));

        self::assertInArray($file, $files);
        self::assertInArray($file2, $files);
        self::assertNotContains($file3, $files);

        self::assertSame([], \iterator_to_array($this->filesystem->files('noop')));
    }

    public function testAllDirectories(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('root');
        $dir2 = $this->createDir('tmp');
        $dir3 = $this->createDir('tmp2');
        $dir4 = $this->createDir('tmp3', 'tmp');

        $directories = \iterator_to_array($this->filesystem->allDirectories($dir));

        self::assertInArray($dir2, $directories);
        self::assertInArray($dir3, $directories);
        self::assertInArray($dir4, $directories);

        self::assertSame([], \iterator_to_array($this->filesystem->allDirectories('noop')));
    }

    public function testCreateDirectoryRecursively(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfChmodIsMissing();

        $dir = $this->createDir('root');
        $this->createDir('directory');

        $dir3 = $dir . \DIRECTORY_SEPARATOR . 'sub_directory';

        $this->filesystem->createDirectory($dir3);

        self::assertDirectoryExists($dir3);
        $this->assertFilePermissions(755, $dir3);

        $dir4 = $dir . \DIRECTORY_SEPARATOR . 'test2';

        $this->filesystem->createDirectory($dir4, ['visibility' => 'private']);

        $this->assertFilePermissions(700, $dir4);
    }

    public function testMkdirCreatesDirectoriesFails(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('root');
        $dir2 = $dir . \DIRECTORY_SEPARATOR . '2';

        $this->expectException(IOException::class);

        \file_put_contents($dir2, '');

        $this->filesystem->createDirectory($dir2);
    }

    /**
     * @return iterable<array<int, float|int|string>>
     */
    public function provideGetAndSetVisibilityCases(): iterable
    {
        yield [400, Permissions::notation('0400'), 753, Permissions::notation('0753')];

        yield [750, Permissions::notation('0770'), 751, Permissions::notation('0753'), 0022];

        yield [600, 'private', 700, 'private'];

        yield [644, 'public', 755, 'public'];
    }

    /**
     * @dataProvider provideGetAndSetVisibilityCases
     *
     * @param int                   $expectedFile
     * @param null|float|int|string $chmodFile
     * @param int                   $expectedDir
     * @param null|float|int|string $chmodDir
     * @param int                   $unmask
     */
    public function testGetAndSetVisibility(
        int $expectedFile,
        $chmodFile,
        int $expectedDir,
        $chmodDir = null,
        int $unmask = 0000
    ): void {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfChmodIsMissing();

        $rootDir = $this->createDir('root');

        $file = $rootDir . \DIRECTORY_SEPARATOR . __FUNCTION__ . $expectedFile . $chmodFile . $chmodDir . $expectedDir . '.txt';

        $this->filesystem->write($file, '');

        $dir = $rootDir . \DIRECTORY_SEPARATOR . __FUNCTION__ . $expectedFile . $chmodFile . $chmodDir . $expectedDir;

        $this->filesystem->createDirectory($dir);

        if ($chmodDir !== null) {
            $this->filesystem->setVisibility($dir, $chmodDir, $unmask);
        }

        if ($chmodFile !== null) {
            $this->filesystem->setVisibility($file, $chmodFile, $unmask);
        }

        self::assertSame($expectedDir, $this->filesystem->getVisibility($dir));
        self::assertSame($expectedFile, $this->filesystem->getVisibility($file));
    }

    public function testSetVisibilityThrowExceptionOnMissingFile(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);

        $this->filesystem->setVisibility('foo', 'public');
    }

    public function testSetVisibilityThrowExceptionOnMissingDir(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);

        $this->filesystem->setVisibility(__DIR__ . '/foo', 'public');
    }

    /**
     * @return iterable<array<int, mixed>>
     */
    public function provideSetVisibilityToThrowInvalidArgumentExceptionCases(): iterable
    {
        yield ['exception'];

        yield [new stdClass()];

        yield [null];
    }

    /**
     * @dataProvider provideSetVisibilityToThrowInvalidArgumentExceptionCases
     *
     * @param mixed $visibility
     */
    public function testSetVisibilityToThrowInvalidArgumentException($visibility): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotSupportedException::class);

        $dir = $this->createDir(__FUNCTION__);

        $this->filesystem->setVisibility($dir, $visibility);
    }

    public function testGetMimeTypeOutputsMimeType(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        if (! \class_exists('Finfo')) {
            self::markTestSkipped('The PHP extension fileinfo is not installed.');
        }

        $this->createDir(__FUNCTION__);

        $file = $this->createFile(__FUNCTION__ . '.txt', 'copy', __FUNCTION__);

        self::assertEquals('text/plain', $this->filesystem->getMimetype($file));
    }

    public function testGetMimetypeToThrowNotFoundException(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);

        $this->filesystem->getMimetype('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'file.php');
    }

    public function testGetLastModified(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->createDir(__FUNCTION__);

        $file = $this->createFile(__FUNCTION__, 'copy', __FUNCTION__);

        /** @var DateTime $datetime */
        $datetime = DateTime::createFromFormat('U', (string) \filemtime($file));

        self::assertSame($datetime->getTimestamp(), $this->filesystem->getLastModified($file)->getTimestamp());
    }

    public function testGetLastModifiedToThrowNotFoundException(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);

        $this->filesystem->getLastModified('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'file.php');
    }

    public function testMoveDirectoryMovesEntireDirectory(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('tmp');
        $temp2 = $this->createDir('root') . \DIRECTORY_SEPARATOR . __FUNCTION__;

        $this->createFile('foo.txt', 'foo', 'tmp');
        $this->createFile('bar.txt', 'bar', 'tmp');

        $this->createDir('nested', 'tmp');
        $this->createFile('baz.txt', 'baz', 'tmp.nested');

        $this->filesystem->move($dir, $temp2);

        self::assertDirectoryExists($temp2);
        self::assertFileExists($temp2 . \DIRECTORY_SEPARATOR . 'foo.txt');
        self::assertFileExists($temp2 . \DIRECTORY_SEPARATOR . 'bar.txt');
        self::assertDirectoryExists($temp2 . \DIRECTORY_SEPARATOR . 'nested');
        self::assertFileExists($temp2 . \DIRECTORY_SEPARATOR . 'nested' . \DIRECTORY_SEPARATOR . 'baz.txt');
        self::assertDirectoryNotExists($dir);
    }

    public function testMoveDirectoryMovesEntireDirectoryAndOverwrites(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('tmp');
        $temp2 = $this->createDir(__FUNCTION__);

        $this->createFile('foo.txt', 'foo', 'tmp');
        $this->createFile('bar.txt', 'bar', 'tmp');

        $this->createDir('nested', 'tmp');
        $this->createFile('baz.txt', 'baz', 'tmp.nested');

        $file = $this->createFile('foo2.txt', 'foo2', __FUNCTION__);
        $file2 = $this->createFile('baz2.txt', 'baz2', __FUNCTION__);

        $this->filesystem->move($dir, $temp2, ['overwrite' => true]);

        self::assertDirectoryExists($temp2);
        self::assertFileExists($temp2 . \DIRECTORY_SEPARATOR . 'foo.txt');
        self::assertFileExists($temp2 . \DIRECTORY_SEPARATOR . 'bar.txt');
        self::assertDirectoryExists($temp2 . \DIRECTORY_SEPARATOR . 'nested');
        self::assertFileExists($temp2 . \DIRECTORY_SEPARATOR . 'nested' . \DIRECTORY_SEPARATOR . 'baz.txt');
        self::assertFileNotExists($file);
        self::assertFileNotExists($file2);
        self::assertDirectoryNotExists($dir);
    }

    public function testCopyDirectoryThrowExceptionIfSourceDirectoryDontExist(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);

        $this->filesystem->copyDirectory(\DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz' . \DIRECTORY_SEPARATOR . 'breeze' . \DIRECTORY_SEPARATOR . 'boom', 'foo');
    }

    public function testCopyDirectoryMovesEntireDirectory(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('tmp');
        $temp2 = $this->createDir('tmp2');

        $this->createFile('foo.txt', 'foo', 'tmp');
        $this->createFile('bar.txt', 'bar', 'tmp');

        $this->createDir('nested', 'tmp');
        $this->createFile('baz.txt', 'baz', 'tmp.nested');

        $this->filesystem->copyDirectory($dir, $temp2);

        self::assertDirectoryExists($temp2);
        self::assertFileExists($temp2 . \DIRECTORY_SEPARATOR . 'foo.txt');
        self::assertFileExists($temp2 . \DIRECTORY_SEPARATOR . 'bar.txt');
        self::assertDirectoryExists($temp2 . \DIRECTORY_SEPARATOR . 'nested');
        self::assertFileExists($temp2 . \DIRECTORY_SEPARATOR . 'nested' . \DIRECTORY_SEPARATOR . 'baz.txt');
    }

    public function testHas(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        self::assertTrue($this->filesystem->has($this->createFile(__FUNCTION__)));
    }

    public function testHasWithInvalidFile(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        self::assertFalse($this->filesystem->has('notfound.txt'));
    }

    public function testHasWithDirectory(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        self::assertTrue($this->filesystem->has($this->createDir(__FUNCTION__)));
    }

    public function testHasThrowException(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Long file names are an issue on Windows');
        }

        $this->expectException(IOException::class);

        $maxPathLength = \PHP_MAXPATHLEN - 2;
        /** @var string $oldPath */
        $oldPath = \getcwd();

        $dir = $this->createDir(__FUNCTION__);
        $file = \str_repeat('T', $maxPathLength - \strlen($dir) + 1);

        $path = $dir . $file;

        $output = $return = null;

        \exec('TYPE NUL >>' . $file, $output, $return); // equivalent of touch, we can not use the php touch() here because it suffers from the same limitation

        $this->longPathNamesWindows[] = $path; // save this so we can clean up later

        \chdir($oldPath);

        $this->filesystem->has($path);
    }

    public function testReadRetrievesFiles(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        self::assertEquals('Foo Bar', $this->filesystem->read($this->createFile(__FUNCTION__, 'Foo Bar')));
    }

    /**
     * @todo fix this test for docker run
     */
    public function testReadThrowIOException(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfChmodIsMissing();

        $file = $this->createFile('temp.txt', 'Foo Bar', null, 0100);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage(\sprintf('Reading failed for [%s]: file_get_contents(%s): failed to open stream:', $file, $file));

        $this->filesystem->read($file);
    }

    public function testReadToThrowException(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Reading failed for [foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'notfound.php], file could not be found.');

        $this->filesystem->read('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'notfound.php');
    }

    public function testReadStreamToThrowException(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Reading failed for [foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'notfound.php], file could not be found.');

        $generator = $this->filesystem->readStream('foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'tmp' . \DIRECTORY_SEPARATOR . 'notfound.php');

        $generator->current(); // throw the exception
    }

    public function testWrite(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir(__FUNCTION__);
        $file = $dir . \DIRECTORY_SEPARATOR . 'foo';

        $this->filesystem->write($file, 'write new');

        self::assertSame('write new', $this->filesystem->read($file));
    }

    /**
     * @return iterable<array<int, int>>
     */
    public function providePermissionCases(): iterable
    {
        //    chmod  umask expected
        yield [0644, 0, 644];

        yield [0600, 022, 600];
    }

    /**
     * @dataProvider providePermissionCases
     *
     * @param int $chmod
     * @param int $umask
     * @param int $expectedChmod
     */
    public function testWriteWithVisibility(int $chmod, int $umask, int $expectedChmod): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfChmodIsMissing();

        $dir = $this->createDir(__FUNCTION__);
        $file = $dir . \DIRECTORY_SEPARATOR . 'write.txt';

        $currentUmask = \umask($umask);

        $this->filesystem->write($file, 'write new visibility', ['visibility' => $chmod, 'umask' => $currentUmask]);

        \umask($currentUmask);

        self::assertSame('write new visibility', $this->filesystem->read($file));
        self::assertSame($expectedChmod, $this->filesystem->getVisibility($file));
    }

    /**
     * @requires OS Darwin|Linux
     *
     * @dataProvider providePermissionCases
     *
     * @param int $chmod
     * @param int $umask
     * @param int $expectedChmod
     */
    public function testWriteStreamWithVisibility(int $chmod, int $umask, int $expectedChmod): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfChmodIsMissing();

        $dir = $this->createDir(__FUNCTION__);

        $file = $dir . \DIRECTORY_SEPARATOR . 'foo_' . $chmod . $umask . $expectedChmod . '.txt';

        /** @var resource $temp */
        $temp = \tmpfile();

        \fwrite($temp, 'dummy');
        \rewind($temp);

        $currentUmask = \umask($umask);

        $this->filesystem->writeStream($file, $temp, ['visibility' => $chmod, 'umask' => $currentUmask]);

        \umask($currentUmask);

        self::assertSame($expectedChmod, $this->filesystem->getVisibility($file));
    }

    /**
     * @dataProvider providePermissionCases
     *
     * @param int $chmod
     * @param int $umask
     * @param int $expectedChmod
     */
    public function testAppendStreamWithVisibility(int $chmod, int $umask, int $expectedChmod): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfChmodIsMissing();

        $file = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'file.php';

        /** @var resource $temp */
        $temp = \tmpfile();

        \fwrite($temp, 'dummy');
        \rewind($temp);

        $currentUmask = \umask($umask);

        $this->filesystem->appendStream($file, $temp, ['visibility' => $chmod, 'umask' => $currentUmask]);

        \umask($currentUmask);

        foreach ($this->filesystem->readStream($file) as $item) {
            self::assertSame(5, \strlen($item));
            self::assertSame('dummy', $item);
        }

        self::assertSame($expectedChmod, $this->filesystem->getVisibility($file));
    }

    public function testWriteStreamAndReadStream(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir(__FUNCTION__);
        $file = $dir . \DIRECTORY_SEPARATOR . __FUNCTION__;

        /** @var resource $temp */
        $temp = \tmpfile();

        \fwrite($temp, 'dummy');
        \rewind($temp);

        $this->filesystem->writeStream($file, $temp);

        $generator = $this->filesystem->readStream($file);

        $array = \iterator_to_array($generator);

        self::assertCount(1, $array);
        self::assertSame('dummy', $array[0]);
    }

    public function testAppend(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'file.php';

        $this->filesystem->append($file, 'test');

        self::assertEquals('test', $this->filesystem->read($file));
    }

    public function testAppendOnExistingFile(): void
    {
        $file = $this->createFile(__FUNCTION__, 'Foo Bar');

        $this->filesystem->append($file, ' test');

        self::assertEquals('Foo Bar test', $this->filesystem->read($file));
    }

    public function testAppendStream(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'file.php';

        /** @var resource $temp */
        $temp = \tmpfile();

        \fwrite($temp, 'dummy');
        \rewind($temp);

        $this->filesystem->appendStream($file, $temp);

        foreach ($this->filesystem->readStream($file) as $item) {
            self::assertSame(5, \strlen($item));
            self::assertSame('dummy', $item);
        }
    }

    public function testAppendStreamOnExistingFile(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createFile(__FUNCTION__ . '.txt', 'Foo Bar');

        /** @var resource $temp */
        $temp = \tmpfile();

        \fwrite($temp, ' dummy');
        \rewind($temp);

        $this->filesystem->appendStream($file, $temp);

        foreach ($this->filesystem->readStream($file) as $item) {
            self::assertSame(13, \strlen($item));
            self::assertSame('Foo Bar dummy', $item);
        }
    }

    public function testCopyCreatesNewFile(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $sourceFilePath = $this->createFile('copy_source_file', 'SOURCE FILE');
        $targetFilePath = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'copy_target_file';

        $this->filesystem->copy($sourceFilePath, $targetFilePath);

        self::assertFileExists($targetFilePath);
        self::assertStringEqualsFile($targetFilePath, 'SOURCE FILE');
    }

    public function testCopyFails(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->expectException(NotFoundException::class);

        $dir = $this->createDir('root');

        $sourceFilePath = $dir . \DIRECTORY_SEPARATOR . 'copy_source_file';
        $targetFilePath = $dir . \DIRECTORY_SEPARATOR . 'copy_target_file';

        $this->filesystem->copy($sourceFilePath, $targetFilePath);
    }

    public function testCopyUnreadableFileFails(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        // skip test on Windows; PHP can't easily set file as unreadable on Windows
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('This test cannot run on Windows.');
        }

        if (! \is_string(\getenv('USER')) || 'root' === \getenv('USER')) {
            self::markTestSkipped('This test will fail if run under superuser');
        }

        $this->expectException(IOException::class);

        $sourceFilePath = $this->createFile('copy_source_file', 'SOURCE FILE');
        $targetFilePath = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'copy_target_file';

        // make sure target cannot be read
        $this->filesystem->setVisibility($sourceFilePath, 0222);
        $this->filesystem->copy($sourceFilePath, $targetFilePath);
    }

    public function testCopyOverridesExistingFileIfModified(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $sourceFilePath = $this->createFile('copy_source_file', 'SOURCE FILE');
        $targetFilePath = $this->createFile('copy_target_file', 'TARGET FILE', null, null, null, \time() - 1000);

        $this->filesystem->copy($sourceFilePath, $targetFilePath);

        self::assertFileExists($targetFilePath);
        self::assertStringEqualsFile($targetFilePath, 'SOURCE FILE');
    }

    public function testCopyDoesNotOverrideExistingFileByDefault(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        // make sure both files have the same modification time
        $modificationTime = \time() - 1000;

        $sourceFilePath = $this->createFile('copy_source_file', 'SOURCE FILE', null, null, null, $modificationTime);
        $targetFilePath = $this->createFile('copy_target_file', 'TARGET FILE', null, null, null, $modificationTime);

        $this->filesystem->copy($sourceFilePath, $targetFilePath);

        self::assertFileExists($targetFilePath);
        self::assertStringEqualsFile($targetFilePath, 'TARGET FILE');
    }

    public function testCopyOverridesExistingFileIfForced(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $sourceFilePath = $this->createFile('copy_source_file', 'SOURCE FILE');
        $targetFilePath = $this->createFile('copy_target_file', 'TARGET FILE');

        // make sure both files have the same modification time
        $modificationTime = \time() - 1000;

        \touch($sourceFilePath, $modificationTime);
        \touch($targetFilePath, $modificationTime);

        $this->filesystem->copy($sourceFilePath, $targetFilePath, true);

        self::assertFileExists($targetFilePath);
        self::assertStringEqualsFile($targetFilePath, 'SOURCE FILE');
    }

    public function testCopyWithOverrideWithReadOnlyTargetFails(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        // skip test on Windows; PHP can't easily set file as unwritable on Windows
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('This test cannot run on Windows.');
        }

        if (! \is_string(\getenv('USER')) || 'root' === \getenv('USER')) {
            self::markTestSkipped('This test will fail if run under superuser');
        }

        $this->expectException(IOException::class);

        $sourceFilePath = $this->createFile('copy_source_file', 'SOURCE FILE');
        $targetFilePath = $this->createFile('copy_target_file', 'TARGET FILE');

        // make sure both files have the same modification time
        $modificationTime = \time() - 1000;

        \touch($sourceFilePath, $modificationTime);
        \touch($targetFilePath, $modificationTime);

        // make sure target is read-only
        $this->filesystem->setVisibility($targetFilePath, 0444);
        $this->filesystem->copy($sourceFilePath, $targetFilePath, true);
    }

    public function testCopyCreatesTargetDirectoryIfItDoesNotExist(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $sourceFilePath = $this->createFile('copy_source_file', 'SOURCE FILE');

        $this->createDir('root');

        $targetFileDirectory = $this->createDir('directory');
        $targetFilePath = $targetFileDirectory . \DIRECTORY_SEPARATOR . 'copy_target_file';

        $this->filesystem->copy($sourceFilePath, $targetFilePath);

        self::assertDirectoryExists($targetFileDirectory);
        self::assertFileExists($targetFilePath);
        self::assertStringEqualsFile($targetFilePath, 'SOURCE FILE');
    }

    /**
     * @group network
     */
    public function testCopyForOriginUrlsAndExistingLocalFileDefaultsToCopy(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        if (! \in_array('https', \stream_get_wrappers(), true)) {
            self::markTestSkipped('"https" stream wrapper is not enabled.');
        }

        $sourceFilePath = 'https://rawcdn.githack.com/narrowspark/art/d5f5f0353dfedf17b9ef2671c3de0c1101e5a439/narrowspark.svg';
        $targetFilePath = $this->createFile('copy_target_file', 'TARGET FILE');

        $this->filesystem->copy($sourceFilePath, $targetFilePath, false);

        self::assertFileExists($targetFilePath);
        self::assertEquals(\file_get_contents($sourceFilePath), \file_get_contents($targetFilePath));
    }

    public function testCopyShouldKeepExecutionPermission(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfChmodIsMissing();

        $sourceFilePath = $this->createFile('copy_source_file', 'SOURCE FILE', null, 0745);
        $targetFilePath = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'copy_target_file';

        $this->filesystem->copy($sourceFilePath, $targetFilePath);

        $this->assertFilePermissions(767, $targetFilePath);
    }

    public function testWithoutExtension(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createFile('tmp.txt');

        self::assertSame('tmp', $this->filesystem->withoutExtension($file, 'txt'));

        $file = $this->createFile('tmp.php');

        self::assertSame('tmp', $this->filesystem->withoutExtension($file));
    }

    public function testGetExtensionReturnsExtension(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createFile('tmp.csv');

        self::assertEquals('csv', $this->filesystem->getExtension($file));
    }

    public function testChangeExtension(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createFile('tmp2.csv');

        self::assertSame(\str_replace('csv', 'php', $file), $this->filesystem->changeExtension($file, 'php'));

        // try on dir
        $dir = $this->createDir('temp3');

        self::assertSame($dir, $this->filesystem->changeExtension($dir, 'php'));
    }

    /**
     * @todo fix this test for docker run
     */
    public function testIsWritable(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfChmodIsMissing();

        $file = $this->createFile('foo.txt', 'foo', null, 0444);

        self::assertFalse($this->filesystem->isWritable($file));

        $this->filesystem->setVisibility($file, 0777);

        self::assertTrue($this->filesystem->isWritable($file));
    }

    public function testIsFile(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir(__FUNCTION__ . 'Dir');

        self::assertFalse($this->filesystem->isFile($dir));

        $file = $this->createFile(__FUNCTION__);

        self::assertTrue($this->filesystem->isFile($file));
    }

    public function testSetOwner(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfPosixIsMissing();

        $dir = $this->createDir(__FUNCTION__);

        $owner = $this->getFileOwner($dir);

        $this->filesystem->setOwner($dir, $owner);

        self::assertSame($owner, $this->getFileOwner($dir));
    }

    public function testSetGroup(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfPosixIsMissing();

        $dir = $this->createDir(__FUNCTION__);

        $group = $this->getFileGroup($dir);

        $this->filesystem->setGroup($dir, $group);

        self::assertSame($group, $this->getFileGroup($dir));
    }

    public function testSetGroupSymlink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        $file = $this->createFile('file');
        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        $this->filesystem->symlink($file, $link);

        $group = $this->getFileGroup($link);

        $this->filesystem->setGroup($link, $group);

        self::assertSame($group, $this->getFileGroup($link));
    }

    public function testSetGroupLink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfLinkIsMissing();

        $file = $this->createFile('file');
        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        $this->filesystem->hardlink($file, $link);

        $group = $this->getFileGroup($link);

        $this->filesystem->setGroup($link, $group);

        self::assertSame($group, $this->getFileGroup($link));
    }

    public function testSetGroupSymlinkFails(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        $this->expectException(IOException::class);

        $file = $this->createFile('file');
        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        $this->filesystem->symlink($file, $link);
        $this->filesystem->setGroup($link, 'user' . \time() . \mt_rand(1000, 9999));
    }

    public function testSetGroupLinkFails(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfLinkIsMissing();

        $this->expectException(IOException::class);

        $file = $this->createFile('file');
        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        $this->filesystem->hardlink($file, $link);
        $this->filesystem->setGroup($link, 'user' . \time() . \mt_rand(1000, 9999));
    }

    public function testSetGroupFail(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfPosixIsMissing();

        $this->expectException(IOException::class);

        $dir = $this->createDir(__FUNCTION__);

        $this->filesystem->setGroup($dir, 'user' . \time() . \mt_rand(1000, 9999));
    }

    public function testSymlink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Windows does not support creating "broken" symlinks');
        }

        $dir = $this->createDir('root');

        $file = $dir . \DIRECTORY_SEPARATOR . 'file';
        $link = $dir . \DIRECTORY_SEPARATOR . 'link';

        // $file does not exist right now: creating "broken" links is a wanted feature
        $this->filesystem->symlink($file, $link);

        self::assertTrue(\is_link($link));

        // Create the linked file AFTER creating the link
        \touch($file);

        self::assertEquals($file, \readlink($link));
    }

    /**
     * @depends testSymlink
     */
    public function testRemoveSymlink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        $this->filesystem->delete($link);

        self::assertFalse(\is_link($link));
        self::assertFalse(\is_file($link));
        self::assertDirectoryNotExists($link);
    }

    public function testSymlinkIsOverwrittenIfPointsToDifferentTarget(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        $dir = $this->createDir('root');

        $file = $this->createFile('file');
        $link = $dir . \DIRECTORY_SEPARATOR . 'link';

        \symlink($dir, $link);

        $this->filesystem->symlink($file, $link);

        self::assertTrue(\is_link($link));
        self::assertEquals($file, \readlink($link));
    }

    public function testSymlinkIsNotOverwrittenIfAlreadyCreated(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        $file = $this->createFile('file');
        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        \symlink($file, $link);

        $this->filesystem->symlink($file, $link);

        self::assertTrue(\is_link($link));
        self::assertEquals($file, \readlink($link));
    }

    public function testSymlinkCreatesTargetDirectoryIfItDoesNotExist(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        $file = $this->createFile('file');

        $this->createDir('root');
        $dir = $this->createDir('dir');
        $dir2 = $this->createDir('subdir', 'dir');

        $link1 = $dir . \DIRECTORY_SEPARATOR . 'link';
        $link2 = $dir2 . \DIRECTORY_SEPARATOR . 'link';

        \touch($file);

        $this->filesystem->symlink($file, $link1);
        $this->filesystem->symlink($file, $link2);

        self::assertTrue(\is_link($link1));
        self::assertEquals($file, \readlink($link1));
        self::assertTrue(\is_link($link2));
        self::assertEquals($file, \readlink($link2));
    }

    public function testLink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfLinkIsMissing();

        $file = $this->createFile('file');
        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        $this->filesystem->hardlink($file, $link);

        self::assertTrue(\is_file($link));
        self::assertEquals(\fileinode($file), \fileinode($link));
    }

    /**
     * @depends testLink
     */
    public function testDeleteWithLink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfLinkIsMissing();

        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        $this->filesystem->delete($link);

        self::assertNotTrue(\is_file($link));
    }

    public function testLinkIsOverwrittenIfPointsToDifferentTarget(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfLinkIsMissing();

        $file = $this->createFile('file');
        $file2 = $this->createFile('file2');
        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        \link($file2, $link);

        $this->filesystem->hardlink($file, $link);

        self::assertTrue(\is_file($link));
        self::assertEquals(\fileinode($file), \fileinode($link));
    }

    public function testLinkIsNotOverwrittenIfAlreadyCreated(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfLinkIsMissing();

        $file = $this->createFile('file');
        $link = $this->createDir('root') . \DIRECTORY_SEPARATOR . 'link';

        \link($file, $link);

        $this->filesystem->hardlink($file, $link);

        self::assertTrue(\is_file($link));
        self::assertEquals(\fileinode($file), \fileinode($link));
    }

    public function testReadRelativeLink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Relative symbolic links are not supported on Windows');
        }

        $dir = $this->createDir('root');

        $file = $this->createFile('file');
        $link1 = $dir . \DIRECTORY_SEPARATOR . 'dir' . \DIRECTORY_SEPARATOR . 'link';
        $link2 = $dir . \DIRECTORY_SEPARATOR . 'dir' . \DIRECTORY_SEPARATOR . 'link2';

        $this->filesystem->symlink('../file', $link1);
        $this->filesystem->symlink('link', $link2);

        self::assertEquals($this->normalize('../file'), $this->filesystem->readlink($link1));
        self::assertEquals('link', $this->filesystem->readlink($link2));
        self::assertEquals($file, $this->filesystem->readlink($link1, true));
        self::assertEquals($file, $this->filesystem->readlink($link2, true));
        self::assertEquals($file, $this->filesystem->readlink($file, true));
    }

    public function testReadAbsoluteLink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        $dir = $this->createDir('root');

        $file = $this->createFile('file');
        $link1 = $dir . \DIRECTORY_SEPARATOR . 'dir' . \DIRECTORY_SEPARATOR . 'link';
        $link2 = $dir . \DIRECTORY_SEPARATOR . 'dir' . \DIRECTORY_SEPARATOR . 'link2';

        $this->filesystem->symlink($file, $link1);
        $this->filesystem->symlink($link1, $link2);

        self::assertEquals($file, $this->filesystem->readlink($link1));
        self::assertEquals($link1, $this->filesystem->readlink($link2));
        self::assertEquals($file, $this->filesystem->readlink($link1, true));
        self::assertEquals($file, $this->filesystem->readlink($link2, true));
        self::assertEquals($file, $this->filesystem->readlink($file, true));
    }

    public function testReadBrokenLink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('Windows does not support creating "broken" symlinks');
        }

        $dir = $this->createDir(__FUNCTION__);

        $file = $dir . \DIRECTORY_SEPARATOR . 'file';
        $link = $dir . \DIRECTORY_SEPARATOR . 'link';

        $this->filesystem->symlink($file, $link);

        self::assertEquals($file, $this->filesystem->readlink($link));
        self::assertNull($this->filesystem->readlink($link, true));

        \touch($file);

        self::assertEquals($file, $this->filesystem->readlink($link, true));
    }

    public function testReadLinkDefaultPathDoesNotExist(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        self::assertNull($this->filesystem->readlink($this->normalize($this->createDir('root') . \DIRECTORY_SEPARATOR . 'invalid')));
    }

    public function testReadLinkDefaultPathNotLink(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $file = $this->createFile(__FUNCTION__);

        self::assertNull($this->filesystem->readlink($file));
    }

    public function testReadLinkCanonicalizePath(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        $this->createDir('root');

        $dir = $this->createDir('dir');
        $file = $this->createFile('file');

        self::assertEquals(
            $file,
            $this->filesystem->readlink($dir . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'file', true)
        );
    }

    public function testReadLinkCanonicalizedPathDoesNotExist(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        self::assertNull($this->filesystem->readlink($this->createDir('root') . \DIRECTORY_SEPARATOR . 'invalid', true));
    }

    public function testMirrorCopiesFilesAndDirectoriesRecursively(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('root');

        $sourcePath = $this->createDir('source');
        $directory = $this->createDir('directory', 'source');

        $file1 = $this->createFile('file1', 'FILE1', 'source.directory');
        $file2 = $this->createFile('file2', 'FILE2', 'source');

        $targetPath = $dir . \DIRECTORY_SEPARATOR . 'target' . \DIRECTORY_SEPARATOR;

        $this->filesystem->mirror($sourcePath, $targetPath);

        self::assertDirectoryExists($targetPath);
        self::assertDirectoryExists($targetPath . 'directory');
        self::assertFileEquals($file1, $targetPath . 'directory' . \DIRECTORY_SEPARATOR . 'file1');
        self::assertFileEquals($file2, $targetPath . 'file2');

        $this->filesystem->delete($file1);
        $this->filesystem->mirror($sourcePath, $targetPath, null, ['delete' => false]);

        self::assertTrue($this->filesystem->has($targetPath . 'directory' . \DIRECTORY_SEPARATOR . 'file1'));

        $this->filesystem->mirror($sourcePath, $targetPath, null, ['delete' => true]);

        self::assertFalse($this->filesystem->has($targetPath . 'directory' . \DIRECTORY_SEPARATOR . 'file1'));

        \file_put_contents($file1, 'FILE1');

        $this->filesystem->mirror($sourcePath, $targetPath, null, ['delete' => true]);

        self::assertTrue($this->filesystem->has($targetPath . 'directory' . \DIRECTORY_SEPARATOR . 'file1'));

        $this->filesystem->deleteDirectory($directory);
        $this->filesystem->mirror($sourcePath, $targetPath, null, ['delete' => true]);

        self::assertFalse($this->filesystem->has($targetPath . 'directory'));
        self::assertFalse($this->filesystem->has($targetPath . 'directory' . \DIRECTORY_SEPARATOR . 'file1'));
    }

    public function testMirrorCreatesEmptyDirectory(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('root');

        $sourcePath = $this->createDir('source') . \DIRECTORY_SEPARATOR;

        $targetPath = $dir . \DIRECTORY_SEPARATOR . 'target' . \DIRECTORY_SEPARATOR;

        $this->filesystem->mirror($sourcePath, $targetPath);

        self::assertDirectoryExists($targetPath);

        $this->filesystem->deleteDirectory($sourcePath);
    }

    public function testMirrorCopiesLinks(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing();

        $dir = $this->createDir('root');

        $sourcePath = $this->createDir('source') . \DIRECTORY_SEPARATOR;

        $file = $this->createFile('file1', 'FILE1', 'source');

        \symlink($file, $sourcePath . 'link1');

        $targetPath = $dir . \DIRECTORY_SEPARATOR . 'target' . \DIRECTORY_SEPARATOR;

        $this->filesystem->mirror($sourcePath, $targetPath);

        self::assertDirectoryExists($targetPath);
        self::assertFileEquals($file, $targetPath . 'link1');
        self::assertTrue(\is_link($targetPath . \DIRECTORY_SEPARATOR . 'link1'));
    }

    public function testMirrorCopiesLinkedDirectoryContents(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing(true);
        $this->markAsSkippedIfChmodIsMissing();

        $dir = $this->createDir('root');

        $sourcePath = $this->createDir('source');
        $nestedPath = $this->createDir('nested', 'source', 0777);

        $this->createFile('file1.txt', 'FILE1', 'source.nested');

        // Note: We symlink directory, not file
        \symlink($nestedPath, $sourcePath . \DIRECTORY_SEPARATOR . 'link1');

        $targetPath = $dir . \DIRECTORY_SEPARATOR . 'target';

        $this->filesystem->mirror($sourcePath, $targetPath);

        self::assertDirectoryExists($targetPath);
        self::assertFileEquals($nestedPath . \DIRECTORY_SEPARATOR . 'file1.txt', $targetPath . \DIRECTORY_SEPARATOR . 'link1' . \DIRECTORY_SEPARATOR . 'file1.txt');
        self::assertTrue(\is_link($targetPath . \DIRECTORY_SEPARATOR . 'link1'));
    }

    public function testMirrorCopiesRelativeLinkedContents(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->markAsSkippedIfSymlinkIsMissing(true);
        $this->markAsSkippedIfChmodIsMissing();

        $dir = $this->createDir('root');
        $sourcePath = $this->createDir('source');

        /** @var string $oldPath */
        $oldPath = \getcwd();

        $nestedPath = $this->createDir('nested', 'source', 0777);

        $this->createFile('file1.txt', 'FILE1', 'source.nested');

        // Note: Create relative symlink
        \chdir($sourcePath);
        \symlink('nested', 'link1');
        \chdir($oldPath);

        $targetPath = $dir . \DIRECTORY_SEPARATOR . 'target';

        $this->filesystem->mirror($sourcePath, $targetPath);

        self::assertDirectoryExists($targetPath);
        self::assertFileEquals($nestedPath . \DIRECTORY_SEPARATOR . 'file1.txt', $targetPath . \DIRECTORY_SEPARATOR . 'link1' . \DIRECTORY_SEPARATOR . 'file1.txt');
        self::assertTrue(\is_link($targetPath . \DIRECTORY_SEPARATOR . 'link1'));
        self::assertEquals(\PHP_OS_FAMILY === 'Windows' ? \realpath($nestedPath) : 'nested', \readlink($targetPath . \DIRECTORY_SEPARATOR . 'link1'));
    }

    public function testMirrorContentsWithSameNameAsSourceOrTargetWithoutDeleteOption(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('root');

        $this->createDir('source');

        $this->createFile('source', '', 'source');
        $this->createFile('target', '', 'source');

        $targetPath = $dir . \DIRECTORY_SEPARATOR . 'target' . \DIRECTORY_SEPARATOR;

        /** @var string $oldPath */
        $oldPath = \getcwd();

        \chdir($dir);

        $this->filesystem->mirror('source', $targetPath);

        \chdir($oldPath);

        self::assertDirectoryExists($targetPath);
        self::assertFileExists($targetPath . 'source');
        self::assertFileExists($targetPath . 'target');
    }

    public function testMirrorContentsWithSameNameAsSourceOrTargetWithDeleteOption(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('root');

        $this->createDir('source');
        $this->createFile('source', null, 'source');

        $targetPath = $this->createDir('target');

        $this->createFile('source', null, 'target');
        $this->createFile('target', null, 'target');

        /** @var string $oldPath */
        $oldPath = \getcwd();

        \chdir($dir);

        $this->filesystem->mirror('source', 'target', null, ['delete' => true]);

        \chdir($oldPath);

        self::assertDirectoryExists($targetPath);
        self::assertFileExists($targetPath . \DIRECTORY_SEPARATOR . 'source');
        self::assertFileNotExists($targetPath . \DIRECTORY_SEPARATOR . 'target');
    }

    public function testMirrorAvoidCopyingTargetDirectoryIfInSourceDirectory(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $this->createDir('root');

        $sourcePath = $this->createDir('source');
        $this->createDir('directory', 'source');

        $file1 = $this->createFile('file1', 'FILE1', 'source.directory');
        $file2 = $this->createFile('file2', 'FILE2', 'source');

        $targetPath = $sourcePath . \DIRECTORY_SEPARATOR . 'target';

        if (\PHP_OS_FAMILY !== 'Windows') {
            $this->filesystem->symlink($targetPath, $sourcePath . 'target_simlink');
        }

        $this->filesystem->mirror($sourcePath, $targetPath, null, ['delete' => true]);

        self::assertTrue($this->filesystem->has($targetPath));
        self::assertTrue($this->filesystem->has($targetPath . \DIRECTORY_SEPARATOR . 'directory'));

        self::assertFileEquals($file1, $targetPath . \DIRECTORY_SEPARATOR . 'directory' . \DIRECTORY_SEPARATOR . 'file1');
        self::assertFileEquals($file2, $targetPath . \DIRECTORY_SEPARATOR . 'file2');

        self::assertFalse($this->filesystem->has($targetPath . \DIRECTORY_SEPARATOR . 'target_simlink'));
        self::assertFalse($this->filesystem->has($targetPath . \DIRECTORY_SEPARATOR . 'target'));
    }

    public function testMirrorFromSubdirectoryInToParentDirectory(): void
    {
        if (\array_key_exists(__FUNCTION__, $this->skippedTests)) {
            self::markTestSkipped($this->skippedTests[__FUNCTION__]);
        }

        $dir = $this->createDir('root');

        $targetPath = $dir . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR;
        $sourcePath = $targetPath . 'bar' . \DIRECTORY_SEPARATOR;

        $file1 = $sourcePath . 'file1';
        $file2 = $sourcePath . 'file2';

        $this->filesystem->createDirectory($sourcePath);

        \file_put_contents($file1, 'FILE1');
        \file_put_contents($file2, 'FILE2');

        $this->filesystem->mirror($sourcePath, $targetPath);

        self::assertFileEquals($file1, $targetPath . 'file1');
    }

    /**
     * @param string      $name
     * @param null|string $content
     * @param null|string $at
     * @param null|int    $chmod
     * @param null|int    $chgrp
     * @param null|int    $time
     *
     * @return string
     */
    abstract protected function createFile(
        string $name,
        ?string $content = null,
        $at = null,
        ?int $chmod = null,
        ?int $chgrp = null,
        ?int $time = null
    ): string;

    /**
     * @param string      $name
     * @param null|string $childOf
     * @param null|int    $chmod
     *
     * @return string
     */
    abstract protected function createDir(string $name, ?string $childOf = null, ?int $chmod = null): string;

    /**
     * @param int $size
     *
     * @return string
     */
    abstract protected function createFileContent(int $size): string;

    /**
     * Normalize the given path (transform each blackslash into a real directory separator).
     *
     * @param string $path
     *
     * @return string
     */
    private function normalize(string $path): string
    {
        return \str_replace('/', \DIRECTORY_SEPARATOR, $path);
    }
}
