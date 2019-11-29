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

namespace Viserio\Component\Filesystem\Test;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class AbstractFilesystemTestCase extends TestCase
{
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

        $this->filesystem->deleteDirectory($this->workspace);

        \umask($this->umask);
    }

    /**
     * @return \Viserio\Contract\Filesystem\Filesystem&\Viserio\Contract\Filesystem\DirectorySystem&\Viserio\Contract\Filesystem\LinkSystem
     */
    abstract protected function getFilesystem();

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
}
