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

namespace Viserio\Component\Filesystem\Tests\Iterator;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Viserio\Component\Filesystem\Iterator\GlobIterator;

/**
 * @internal
 *
 * @small
 */
final class GlobIteratorTest extends AbstractBaseGlobFixtureTestCase
{
    public function testIterate(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/*.css');

        $this->assertSameAfterSorting([
            $this->root->url() . '/base.css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateEscaped(): void
    {
        if (\defined('PHP_WINDOWS_VERSION_MAJOR')) {
            self::markTestSkipped('A "*" in filenames is not supported on Windows.');
        }

        \touch($this->root->url() . '/css/style*.css');

        $iterator = new GlobIterator($this->root->url() . '/css/style\\*.css');

        $this->assertSameAfterSorting([
            $this->root->url() . '/css/style*.css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateSpecialChars(): void
    {
        if (\defined('PHP_WINDOWS_VERSION_MAJOR')) {
            self::markTestSkipped('A "*" in filenames is not supported on Windows.');
        }

        \touch($this->root->url() . '/css/style*.css');

        $iterator = new GlobIterator($this->root->url() . '/css/style*.css');

        $this->assertSameAfterSorting([
            $this->root->url() . '/css/style*.css',
            $this->root->url() . '/css/style.css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateDoubleWildcard(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/**/*.css');

        $this->assertSameAfterSorting([
            $this->root->url() . '/base.css',
            $this->root->url() . '/css/reset.css',
            $this->root->url() . '/css/style.css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateSingleDirectory(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/css');

        self::assertSame([
            $this->root->url() . '/css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateSingleFile(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/css/style.css');

        self::assertSame([
            $this->root->url() . '/css/style.css',
        ], \iterator_to_array($iterator));
    }

    public function testIterateSingleFileInDirectoryWithUnreadableFiles(): void
    {
        $this->root->addChild(new vfsStreamDirectory('temp'));

        $file = vfsStream::newFile('script.js')
            ->at($this->root->getChild('temp'));

        $iterator = new GlobIterator($file->url());

        self::assertSame([
            $file->url(),
        ], \iterator_to_array($iterator));
    }

    public function testWildcardMayMatchZeroCharacters(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/*css');

        $this->assertSameAfterSorting([
            $this->root->url() . '/base.css',
            $this->root->url() . '/css',
        ], \iterator_to_array($iterator));
    }

    public function testDoubleWildcardMayMatchZeroCharacters(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/**/*css');

        $this->assertSameAfterSorting([
            $this->root->url() . '/base.css',
            $this->root->url() . '/css',
            $this->root->url() . '/css/reset.css',
            $this->root->url() . '/css/style.css',
        ], \iterator_to_array($iterator));
    }

    public function testWildcardInRoot(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/*');

        $this->assertSameAfterSorting([
            $this->root->url() . '/base.css',
            $this->root->url() . '/css',
            $this->root->url() . '/js',
        ], \iterator_to_array($iterator));
    }

    public function testDoubleWildcardInRoot(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/**/*');

        $this->assertSameAfterSorting([
            $this->root->url() . '/base.css',
            $this->root->url() . '/css',
            $this->root->url() . '/css/reset.css',
            $this->root->url() . '/css/style.css',
            $this->root->url() . '/css/style.cts',
            $this->root->url() . '/css/style.cxs',
            $this->root->url() . '/js',
            $this->root->url() . '/js/script.js',
        ], \iterator_to_array($iterator));
    }

    public function testNoMatches(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/foo*');

        self::assertSame([], \iterator_to_array($iterator));
    }

    public function testNonExistingBaseDirectory(): void
    {
        $iterator = new GlobIterator($this->root->url() . '/foo/*');

        self::assertSame([], \iterator_to_array($iterator));
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
