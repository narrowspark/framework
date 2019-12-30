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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Finder\Iterator\GlobIterator;
use Viserio\Component\Finder\SplFileInfo;
use function Viserio\Component\Finder\glob;

/**
 * @covers \Viserio\Component\Finder\Iterator\GlobIterator
 *
 * @internal
 *
 * @small
 */
final class GlobIteratorTest extends TestCase
{
    /** @var string */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = \dirname(__DIR__) . '/Fixture/Iterator';
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        $rootPath = $this->path . \DIRECTORY_SEPARATOR . 'root';

        if (\is_dir($rootPath)) {
            foreach (glob($rootPath . '/css/style*.css') as $file) {
                \unlink($file);
            }

            \rmdir($rootPath . '/css');
            \rmdir($rootPath);
        }

        $cssPath = $this->path . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'style*.css';

        if (\is_file($cssPath)) {
            \unlink($cssPath);
        }
    }

    public function testIterate(): void
    {
        $iterator = new GlobIterator($this->path . '/*.css');

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateEscaped(): void
    {
        if (\defined('PHP_WINDOWS_VERSION_MAJOR')) {
            self::markTestSkipped('A "*" in filenames is not supported on Windows.');
        }

        $path = $this->path;

        \touch($path . '/css/style*.css');

        $iterator = new GlobIterator($path . '/css/style\\*.css');

        $this->assertSameAfterSorting([
            $path . '/css/style*.css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateSpecialChars(): void
    {
        if (\defined('PHP_WINDOWS_VERSION_MAJOR')) {
            self::markTestSkipped('A "*" in filenames is not supported on Windows.');
        }

        $path = $this->path;

        \touch($path . '/css/style*.css');

        $iterator = new GlobIterator($path . '/css/style*.css');

        $this->assertSameAfterSorting([
            $path . '/css/style*.css',
            $path . '/css/style.css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateDoubleWildcard(): void
    {
        $iterator = new GlobIterator($this->path . '/**/*.css');

        $iterator = \array_map(static function (SplFileInfo $file) {
            return $file->getNormalizedPathname();
        }, \iterator_to_array($iterator));

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
            $this->path . '/css/reset.css',
            $this->path . '/css/style.css',
        ], $iterator);
    }

    public function testIterateSingleDirectory(): void
    {
        $iterator = new GlobIterator($this->path . '/css');

        self::assertSame([
            $this->path . '/css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateSingleFile(): void
    {
        $iterator = new GlobIterator($this->path . '/css/style.css');

        self::assertSame([
            $this->path . '/css/style.css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateSingleFileInDirectoryWithUnreadableFiles(): void
    {
        $file = \tempnam(\sys_get_temp_dir(), __FUNCTION__);

        $iterator = new GlobIterator($file);

        try {
            $this->assertSameAfterSorting([
                $file,
            ], \iterator_to_array($iterator));
        } finally {
            @\unlink($file);
        }
    }

    public function testWildcardMayMatchZeroCharacters(): void
    {
        $iterator = new GlobIterator($this->path . '/*css');

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
            $this->path . '/css',
        ], \iterator_to_array($iterator));
    }

    public function testDoubleWildcardMayMatchZeroCharacters(): void
    {
        $iterator = new GlobIterator($this->path . '/**/*css');

        $iterator = \array_map(static function (SplFileInfo $file) {
            return $file->getNormalizedPathname();
        }, \iterator_to_array($iterator));

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
            $this->path . '/css',
            $this->path . '/css/reset.css',
            $this->path . '/css/style.css',
        ], $iterator);
    }

    public function testWildcardInRoot(): void
    {
        $iterator = new GlobIterator($this->path . '/*');

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
            $this->path . '/css',
            $this->path . '/js',
        ], \iterator_to_array($iterator));
    }

    public function testDoubleWildcardInRoot(): void
    {
        $iterator = new GlobIterator($this->path . '/**/*');

        $iterator = \array_map(static function (SplFileInfo $file) {
            return $file->getNormalizedPathname();
        }, \iterator_to_array($iterator));

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
            $this->path . '/css',
            $this->path . '/css/reset.css',
            $this->path . '/css/style.css',
            $this->path . '/css/style.cts',
            $this->path . '/css/style.cxs',
            $this->path . '/js',
            $this->path . '/js/script.js',
        ], $iterator);
    }

    public function testNoMatches(): void
    {
        $iterator = new GlobIterator($this->path . '/foo*');

        self::assertSame([], \iterator_to_array($iterator));
    }

    public function testNonExistingBaseDirectory(): void
    {
        $iterator = new GlobIterator($this->path . '/foo/*');

        self::assertSame([], \iterator_to_array($iterator));
    }

    public function testGlobEscape(): void
    {
        if (\defined('PHP_WINDOWS_VERSION_MAJOR')) {
            self::markTestSkipped('A "*" in filenames is not supported on Windows.');
        }

        $rootPath = $this->path . '/root';

        @\mkdir($rootPath, 0777);
        @\mkdir($rootPath . '/css', 0777);

        \touch($rootPath . '/css/style.css');
        \touch($rootPath . '/css/style*.css');
        \touch($rootPath . '/css/style{.css');
        \touch($rootPath . '/css/style}.css');
        \touch($rootPath . '/css/style?.css');
        \touch($rootPath . '/css/style[.css');
        \touch($rootPath . '/css/style].css');
        \touch($rootPath . '/css/style^.css');

        self::assertSame([
            $rootPath . '/css/style*.css',
            $rootPath . '/css/style.css',
            $rootPath . '/css/style?.css',
            $rootPath . '/css/style[.css',
            $rootPath . '/css/style].css',
            $rootPath . '/css/style^.css',
            $rootPath . '/css/style{.css',
            $rootPath . '/css/style}.css',
        ], glob($rootPath . '/css/style*.css'));
        self::assertSame([
            $rootPath . '/css/style*.css',
        ], glob($rootPath . '/css/style\\*.css'));
        self::assertSame([
            $rootPath . '/css/style{.css',
        ], glob($rootPath . '/css/style\\{.css'));
        self::assertSame([
            $rootPath . '/css/style}.css',
        ], glob($rootPath . '/css/style\\}.css'));
        self::assertSame([
            $rootPath . '/css/style?.css',
        ], glob($rootPath . '/css/style\\?.css'));
        self::assertSame([
            $rootPath . '/css/style[.css',
        ], glob($rootPath . '/css/style\\[.css'));
        self::assertSame([
            $rootPath . '/css/style].css',
        ], glob($rootPath . '/css/style\\].css'));
        self::assertSame([
            $rootPath . '/css/style^.css',
        ], glob($rootPath . '/css/style\\^.css'));
    }

    public function testNativeGlobThrowsExceptionIfUnclosedBrace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // native impl
        self::assertSame([], glob($this->path . '/*.cs{t,s'));
    }

    public function testCustomGlobThrowsExceptionIfUnclosedBrace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // custom impl
        self::assertSame([], glob($this->path . '/**/*.cs{t,s'));
    }

    public function testNativeGlobThrowsExceptionIfUnclosedBracket(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // native impl
        self::assertSame([], glob($this->path . '/*.cs[ts'));
    }

    public function testCustomGlobThrowsExceptionIfUnclosedBracket(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // custom impl
        self::assertSame([], glob($this->path . '/**/*.cs[ts'));
    }

    public function testGlobFailsIfNotAbsolute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('*.css');

        glob('*.css');
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
    private function assertSameAfterSorting($expected, $actual, $message = ''): void
    {
        if (\is_array($actual)) {
            \sort($actual);
        }

        self::assertSame($expected, $actual, $message);
    }
}
