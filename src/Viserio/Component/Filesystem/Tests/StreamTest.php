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

use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Stream;
use function Viserio\Component\Finder\glob;

/**
 * @covers \Viserio\Component\Filesystem\Stream
 *
 * @internal
 *
 * @small
 */
final class StreamTest extends TestCase
{
    private const COUNT_FILES = 300;

    /** @var string */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = Stream::PROTOCOL . '://test.txt';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        @\unlink($this->path);
    }

    public function testBasicBehavior(): void
    {
        /** @var resource $handle actually it creates temporary file */
        $handle = \fopen(Stream::PROTOCOL . '://myfile.txt', 'x');

        \fwrite($handle, 'atomic and safe');

        // and now rename it
        \fclose($handle);

        self::assertTrue(\is_file(Stream::PROTOCOL . '://myfile.txt'));
        self::assertSame('atomic and safe', \file_get_contents(Stream::PROTOCOL . '://myfile.txt'));

        // removes file thread-safe way
        \unlink(Stream::PROTOCOL . '://myfile.txt');

        // this is not thread safe - don't relay on returned value
        self::assertFalse(\is_file(Stream::PROTOCOL . '://myfile.txt'));
    }

    public function testCreateAndDelete(): void
    {
        \file_put_contents($this->path, 'hello');

        self::assertFileExists($this->path);

        self::assertTrue(@\unlink($this->path));
        self::assertFileNotExists($this->path);
        self::assertFalse(\is_file($this->path));
    }

    public function testTouch(): void
    {
        self::assertTrue(@\touch($this->path));

        /** @var int $fileTime */
        $fileTime = \filemtime($this->path);

        self::assertTrue(@\touch($this->path, \time() - 1000));

        \clearstatcache();

        self::assertNotSame(\filemtime($this->path), $fileTime);

        \clearstatcache();

        $metadata = \stat($this->path);

        if ($metadata === false) {
            self::fail(\sprintf('stat function didnt return any file information [%s]', $this->path));
        }

        /** @noinspection PotentialMalwareInspection */
        self::assertTrue(@\touch($this->path, $fileTime, \time() + 1000));

        \clearstatcache();

        $metadata2 = \stat($this->path);

        if ($metadata2 === false) {
            self::fail(\sprintf('stat function didnt return any file information [%s]', $this->path));
        }

        self::assertNotSame($metadata2[8] /* atime */, $metadata[8] /* atime */);
        self::assertTrue(\is_file($this->path));
    }

    public function testChmod(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('chmod is not supported on Windows');
        }

        \file_put_contents($this->path, 'hello');

        self::assertTrue(@\chmod($this->path, 0777));
        self::assertEquals('100777', \sprintf('%o', \fileperms($this->path)));
    }

    public function testChown(): void
    {
        $this->markAsSkippedIfPosixIsMissing();

        \file_put_contents($this->path, 'hello');

        $owner = $this->getFileOwner($this->path);

        \chown($this->path, $owner);

        self::assertSame($owner, $this->getFileOwner($this->path));
    }

    public function testChgrp(): void
    {
        $this->markAsSkippedIfPosixIsMissing();

        \file_put_contents($this->path, 'hello');

        $group = $this->getFileGroup($this->path);

        \chgrp($this->path, $group);

        self::assertSame($group, $this->getFileGroup($this->path));
        self::assertTrue(\is_file($this->path));
    }

    /**
     * @return iterable<array<int, bool|int>>
     */
    public static function provideStressCases(): iterable
    {
        yield [true, 300];

        yield [false, 0];
    }

    /**
     * @dataProvider provideStressCases
     *
     * @param bool $delete
     * @param int  $notFoundCounter
     *
     * @throws Exception
     */
    public function testStress(bool $delete, int $notFoundCounter): void
    {
        $tempDir = __DIR__ . '/tmp/' . \lcg_value();
        $counter = 0;

        try {
            @\mkdir($tempDir, 0777, true);

            \set_time_limit(0);

            $hits = ['ok' => 0, 'notfound' => 0, 'error' => 0, 'cantwrite' => 0, 'cantdelete' => 0];

            for (; $counter < self::COUNT_FILES; $counter++) {
                $random = \random_int(0, self::COUNT_FILES);

                if (@\file_put_contents(Stream::PROTOCOL . '://' . $tempDir . '/testfile' . $random, $this->generateRandomString()) === false) {
                    $hits['cantwrite']++;
                }

                if ($delete && ! @\unlink(Stream::PROTOCOL . '://' . $tempDir . '/testfile' . $random)) {
                    $hits['cantdelete']++;
                }

                $res = @\file_get_contents(Stream::PROTOCOL . '://' . $tempDir . '/testfile' . $random);

                // compare
                if ($res === false) {
                    $hits['notfound']++;
                } elseif ($this->checkStr($res)) {
                    $hits['ok']++;
                } else {
                    $hits['error']++;
                }
            }

            self::assertEquals([
                'ok' => $counter - $notFoundCounter, // should be 1000. If unlink() is used, sum [ok] + [notfound] should be 1000
                'notfound' => $notFoundCounter,      // means 'file not found', should be 0 if unlink() is not used
                'error' => 0,                        // means 'file contents is damaged', MUST be 0
                'cantwrite' => 0,                    // means 'somebody else is writing this file'
                'cantdelete' => 0,                   // means 'unlink() has timeout', should be 0
            ], $hits);
        } finally {
            \array_map(static function (string $value): void {
                @\unlink($value);
            }, glob($tempDir . '/*', \GLOB_NOSORT));

            @\rmdir($tempDir);
            @\rmdir(dirname($tempDir));
        }
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
     * Check if posix_isatty is supported, if not skip the test.
     *
     * @return void
     */
    private function markAsSkippedIfPosixIsMissing(): void
    {
        if (! \function_exists('posix_isatty')) {
            self::markTestSkipped('Function posix_isatty is required.');
        }
    }

    private function generateRandomString(): string
    {
        $s = \str_repeat('LaTrine', \mt_rand(100, 20000));

        return \md5($s, true) . $s;
    }

    /**
     * @param string $s
     *
     * @return bool
     */
    private function checkStr(string $s): bool
    {
        return \strpos($s, \md5(\substr($s, 16), true)) === 0;
    }
}
