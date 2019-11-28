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

use RecursiveIteratorIterator;
use UnexpectedValueException;
use Viserio\Component\Filesystem\Iterator\RecursiveDirectoryIterator;

/**
 * @internal
 *
 * @small
 */
final class RecursiveDirectoryIteratorTest extends AbstractBaseGlobFixtureTestCase
{
    public function testIterate(): void
    {
        $iterator = new RecursiveDirectoryIterator(
            $this->root->url(),
            RecursiveDirectoryIterator::CURRENT_AS_PATHNAME
        );
        $this->assertSameAfterSorting([
            $this->root->url() . '/.' => $this->root->url() . '/.',
            $this->root->url() . '/..' => $this->root->url() . '/..',
            $this->root->url() . '/base.css' => $this->root->url() . '/base.css',
            $this->root->url() . '/css' => $this->root->url() . '/css',
            $this->root->url() . '/js' => $this->root->url() . '/js',
        ], \iterator_to_array($iterator));
    }

    public function testIterateSkipDots(): void
    {
        $iterator = new RecursiveDirectoryIterator(
            $this->root->url(),
            RecursiveDirectoryIterator::CURRENT_AS_PATHNAME | RecursiveDirectoryIterator::SKIP_DOTS
        );
        $this->assertSameAfterSorting([
            $this->root->url() . '/base.css' => $this->root->url() . '/base.css',
            $this->root->url() . '/css' => $this->root->url() . '/css',
            $this->root->url() . '/js' => $this->root->url() . '/js',
        ], \iterator_to_array($iterator));
    }

    public function testIterateTrailingSlash(): void
    {
        $iterator = new RecursiveDirectoryIterator(
            $this->root->url() . '/',
            RecursiveDirectoryIterator::CURRENT_AS_PATHNAME
        );
        $this->assertSameAfterSorting([
            $this->root->url() . '/.' => $this->root->url() . '/.',
            $this->root->url() . '/..' => $this->root->url() . '/..',
            $this->root->url() . '/base.css' => $this->root->url() . '/base.css',
            $this->root->url() . '/css' => $this->root->url() . '/css',
            $this->root->url() . '/js' => $this->root->url() . '/js',
        ], \iterator_to_array($iterator));
    }

    public function testIterateRecursively(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->root->url(),
                RecursiveDirectoryIterator::CURRENT_AS_PATHNAME
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );
        $this->assertSameAfterSorting([
            $this->root->url() . '/.' => $this->root->url() . '/.',
            $this->root->url() . '/..' => $this->root->url() . '/..',
            $this->root->url() . '/base.css' => $this->root->url() . '/base.css',
            $this->root->url() . '/css' => $this->root->url() . '/css',
            $this->root->url() . '/css/.' => $this->root->url() . '/css/.',
            $this->root->url() . '/css/..' => $this->root->url() . '/css/..',
            $this->root->url() . '/css/reset.css' => $this->root->url() . '/css/reset.css',
            $this->root->url() . '/css/style.css' => $this->root->url() . '/css/style.css',
            $this->root->url() . '/css/style.cts' => $this->root->url() . '/css/style.cts',
            $this->root->url() . '/css/style.cxs' => $this->root->url() . '/css/style.cxs',
            $this->root->url() . '/js' => $this->root->url() . '/js',
            $this->root->url() . '/js/.' => $this->root->url() . '/js/.',
            $this->root->url() . '/js/..' => $this->root->url() . '/js/..',
            $this->root->url() . '/js/script.js' => $this->root->url() . '/js/script.js',
        ], \iterator_to_array($iterator));
    }

    public function testFailIfNonExistingBaseDirectory(): void
    {
        $this->expectException(UnexpectedValueException::class);

        new RecursiveDirectoryIterator($this->root->url() . '/foobar');
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
        if (\is_array($expected)) {
            \ksort($expected);
        }

        if (\is_array($actual)) {
            \ksort($actual);
        }
        self::assertSame($expected, $actual, $message);
    }
}
