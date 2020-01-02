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

namespace Viserio\Component\Finder\Tests\Iterator;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator;
use UnexpectedValueException;
use Viserio\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Viserio\Component\Finder\SplFileInfo;
use Viserio\Contract\Finder\Exception\RuntimeException;

/**
 * @covers \Viserio\Component\Finder\Iterator\RecursiveDirectoryIterator
 *
 * @internal
 *
 * @small
 */
final class RecursiveDirectoryIteratorTest extends TestCase
{
    /** @var string */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Iterator';
    }

    public function testIterate(): void
    {
        $basePath = $this->path;

        $iterator = new RecursiveDirectoryIterator(
            $basePath,
            RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
        );

        $this->assertSameAfterSorting([
            $basePath . \DIRECTORY_SEPARATOR . '.' => $basePath . \DIRECTORY_SEPARATOR . '.',
            $basePath . \DIRECTORY_SEPARATOR . '..' => $basePath . \DIRECTORY_SEPARATOR . '..',
            $basePath . \DIRECTORY_SEPARATOR . 'base.css' => $basePath . \DIRECTORY_SEPARATOR . 'base.css',
            $basePath . \DIRECTORY_SEPARATOR . 'css' => $basePath . \DIRECTORY_SEPARATOR . 'css',
            $basePath . \DIRECTORY_SEPARATOR . 'js' => $basePath . \DIRECTORY_SEPARATOR . 'js',
        ], \iterator_to_array($iterator));
    }

    public function testIterateSkipDots(): void
    {
        $basePath = $this->path;

        $iterator = new RecursiveDirectoryIterator(
            $basePath,
            RecursiveDirectoryIterator::CURRENT_AS_FILEINFO | RecursiveDirectoryIterator::SKIP_DOTS
        );

        $this->assertSameAfterSorting([
            $basePath . \DIRECTORY_SEPARATOR . 'base.css' => $basePath . \DIRECTORY_SEPARATOR . 'base.css',
            $basePath . \DIRECTORY_SEPARATOR . 'css' => $basePath . \DIRECTORY_SEPARATOR . 'css',
            $basePath . \DIRECTORY_SEPARATOR . 'js' => $basePath . \DIRECTORY_SEPARATOR . 'js',
        ], \iterator_to_array($iterator));
    }

    public function testIterateTrailingSlash(): void
    {
        $basePath = $this->path;

        $iterator = new RecursiveDirectoryIterator(
            $basePath . \DIRECTORY_SEPARATOR . '',
            RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
        );

        $this->assertSameAfterSorting([
            $basePath . \DIRECTORY_SEPARATOR . '.' => $basePath . \DIRECTORY_SEPARATOR . '.',
            $basePath . \DIRECTORY_SEPARATOR . '..' => $basePath . \DIRECTORY_SEPARATOR . '..',
            $basePath . \DIRECTORY_SEPARATOR . 'base.css' => $basePath . \DIRECTORY_SEPARATOR . 'base.css',
            $basePath . \DIRECTORY_SEPARATOR . 'css' => $basePath . \DIRECTORY_SEPARATOR . 'css',
            $basePath . \DIRECTORY_SEPARATOR . 'js' => $basePath . \DIRECTORY_SEPARATOR . 'js',
        ], \iterator_to_array($iterator));
    }

    public function testIterateRecursively(): void
    {
        $basePath = $this->path;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $basePath,
                RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $this->assertSameAfterSorting([
            $basePath . \DIRECTORY_SEPARATOR . '.' => $basePath . \DIRECTORY_SEPARATOR . '.',
            $basePath . \DIRECTORY_SEPARATOR . '..' => $basePath . \DIRECTORY_SEPARATOR . '..',
            $basePath . \DIRECTORY_SEPARATOR . 'base.css' => $basePath . \DIRECTORY_SEPARATOR . 'base.css',
            $basePath . \DIRECTORY_SEPARATOR . 'css' => $basePath . \DIRECTORY_SEPARATOR . 'css',
            $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . '.' => $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . '.',
            $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . '..' => $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . '..',
            $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'reset.css' => $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'reset.css',
            $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'style.css' => $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'style.css',
            $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'style.cts' => $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'style.cts',
            $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'style.cxs' => $basePath . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'style.cxs',
            $basePath . \DIRECTORY_SEPARATOR . 'js' => $basePath . \DIRECTORY_SEPARATOR . 'js',
            $basePath . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . '.' => $basePath . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . '.',
            $basePath . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . '..' => $basePath . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . '..',
            $basePath . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . 'script.js' => $basePath . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . 'script.js',
        ], \iterator_to_array($iterator));
    }

    public function testFailIfNonExistingBaseDirectory(): void
    {
        $this->expectException(UnexpectedValueException::class);

        new RecursiveDirectoryIterator($this->path . \DIRECTORY_SEPARATOR . 'foobar');
    }

    public function testConstructorThrowsExceptionIfANotSupportedFlagIsGiven(): void
    {
        $this->expectException(RuntimeException::class);

        new RecursiveDirectoryIterator($this->path, FilesystemIterator::CURRENT_AS_PATHNAME);
    }

    /**
     * @group network
     */
    public function testRewindOnFtp(): void
    {
        try {
            $i = new RecursiveDirectoryIterator('ftp://speedtest.tele2.net/', RecursiveDirectoryIterator::SKIP_DOTS);
        } catch (UnexpectedValueException $e) {
            self::markTestSkipped('Unsupported stream "ftp".');
        }

        $i->rewind();

        $this->expectNotToPerformAssertions();
    }

    /**
     * @group network
     */
    public function testSeekOnFtp(): void
    {
        try {
            $i = new RecursiveDirectoryIterator('ftp://speedtest.tele2.net/', RecursiveDirectoryIterator::SKIP_DOTS);
        } catch (UnexpectedValueException $e) {
            self::markTestSkipped('Unsupported stream "ftp".');
        }

        $contains = [
            'ftp://speedtest.tele2.net' . \DIRECTORY_SEPARATOR . '1000GB.zip',
            'ftp://speedtest.tele2.net' . \DIRECTORY_SEPARATOR . '100GB.zip',
        ];

        $actual = [];

        $i->seek(0);

        $actual[] = $i->getPathname();

        $i->seek(1);

        $actual[] = $i->getPathname();

        self::assertEquals($contains, $actual);
    }

    /**
     * Compares that an array is the same as another after sorting.
     *
     * This is necessary since RecursiveDirectoryIterator is not guaranteed to
     * return sorted results on all filesystems.
     *
     * @param mixed  $expected
     * @param mixed  $actual
     * @param string $message
     */
    private function assertSameAfterSorting($expected, $actual, string $message = ''): void
    {
        if (\is_array($expected)) {
            \ksort($expected);
        }

        if (\is_array($actual)) {
            $actual = \array_map(static function (SplFileInfo $file): string {
                return $file->getPathname();
            }, $actual);

            \ksort($actual);
        }

        self::assertSame($expected, $actual, $message);
    }
}
