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

namespace Viserio\Component\View\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\View\ViewFinder;
use Viserio\Contract\View\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class ViewFinderTest extends MockeryTestCase
{
    /** @var \Viserio\Component\View\ViewFinder */
    private $finder;

    /** @var string */
    private $path;

    /** @var int */
    private $count = 0;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture';

        $this->finder = new ViewFinder(
            [
                'viserio' => [
                    'view' => [
                        'paths' => [$this->path],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        StaticMemory::$fileExists = null;
        $this->count = 0;
        $this->finder = null;
    }

    public function testBasicViewFinding(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo.php';

        StaticMemory::$fileExists = static function ($file) use ($path) {
            if ($file === $path) {
                return true;
            }

            return false;
        };

        self::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
        // cache test
        self::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
    }

    public function testCascadingFileLoading(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo.phtml';

        StaticMemory::$fileExists = function ($file) use ($path) {
            $this->count++;

            if ($file === $path) {
                return true;
            }

            return false;
        };

        self::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
        self::assertSame(2, $this->count);
    }

    public function testDirectoryCascadingFileLoading(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'Nested' . \DIRECTORY_SEPARATOR . 'foo.php';

        $this->finder->addLocation($this->path . \DIRECTORY_SEPARATOR . 'Nested');

        $files = [];

        StaticMemory::$fileExists = function ($file) use ($path, &$files) {
            $this->count++;
            $files[] = $file;

            if ($file === $path) {
                return true;
            }

            return false;
        };

        self::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
        self::assertSame(6, $this->count);
        self::assertSame(
            [
                $this->path . \DIRECTORY_SEPARATOR . 'foo.php',
                $this->path . \DIRECTORY_SEPARATOR . 'foo.phtml',
                $this->path . \DIRECTORY_SEPARATOR . 'foo.css',
                $this->path . \DIRECTORY_SEPARATOR . 'foo.js',
                $this->path . \DIRECTORY_SEPARATOR . 'foo.md',
                $path,
            ],
            $files
        );
    }

    public function testNamespacedBasicFileLoading(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.php';

        $this->finder->addNamespace(
            'foo',
            $this->path . \DIRECTORY_SEPARATOR . 'foo'
        );

        StaticMemory::$fileExists = static function ($file) use ($path) {
            if ($file === $path) {
                return true;
            }

            return false;
        };

        self::assertEquals(
            $path,
            $this->finder->find('foo::bar.baz')['path']
        );
    }

    public function testCascadingNamespacedFileLoading(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.php';

        $this->finder->addNamespace(
            'foo',
            $this->path . \DIRECTORY_SEPARATOR . 'foo'
        );

        StaticMemory::$fileExists = static function ($file) use ($path) {
            if ($file === $path) {
                return true;
            }

            return false;
        };

        self::assertEquals(
            $path,
            $this->finder->find('foo::bar.baz')['path']
        );
        self::assertEquals(
            'bar' . \DIRECTORY_SEPARATOR . 'baz.php',
            $this->finder->find('foo::bar.baz')['name']
        );
    }

    public function testDirectoryCascadingNamespacedFileLoading(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.php';
        $path2 = $this->path . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.php';
        $path3 = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.phtml';
        $path4 = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.css';
        $path5 = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.js';
        $path6 = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.md';

        $this->finder->addNamespace(
            'foo',
            [
                $this->path . \DIRECTORY_SEPARATOR . 'foo',
                $this->path . \DIRECTORY_SEPARATOR . 'bar',
            ]
        );
        $this->finder->addNamespace(
            'foo',
            $this->path . \DIRECTORY_SEPARATOR . 'baz'
        );

        $files = [];

        StaticMemory::$fileExists = function ($file) use ($path2, &$files) {
            $this->count++;
            $files[] = $file;

            if ($file === $path2) {
                return true;
            }

            return false;
        };

        self::assertEquals(
            $path2,
            $this->finder->find('foo::bar.baz')['path']
        );
        self::assertSame(
            [
                $path,
                $path3,
                $path4,
                $path5,
                $path6,
                $path2,
            ],
            $files
        );
        self::assertSame(6, $this->count);
    }

    public function testSetAndGetPaths(): void
    {
        $this->finder->setPaths(['test', 'foo']);

        self::assertCount(2, $this->finder->getPaths());
    }

    public function testExceptionThrownWhenViewNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('View [foo] not found.');

        StaticMemory::$fileExists = static function () {
            return false;
        };

        $this->finder->find('foo');
    }

    public function testExceptionThrownWhenViewHasAInvalidName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('View [foo::foo::] has an invalid name.');

        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo.php';

        StaticMemory::$fileExists = static function ($file) use ($path) {
            if ($file === $path) {
                return true;
            }

            return false;
        };

        self::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );

        $this->finder->find('foo::foo::');
    }

    public function testExceptionThrownOnInvalidViewName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No hint path defined for [name].');

        $this->finder->find('name::');
    }

    public function testExceptionThrownWhenNoHintPathIsRegistered(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No hint path defined for [name].');

        $this->finder->find('name::foo');
    }

    public function testAddingExtensionPrependsNotAppends(): void
    {
        $this->finder->addExtension('baz');
        $extensions = $this->finder->getExtensions();

        self::assertEquals('baz', \reset($extensions));
    }

    public function testAddingExtensionsReplacesOldOnes(): void
    {
        $this->finder->addExtension('baz');
        $this->finder->addExtension('baz');

        self::assertCount(6, $this->finder->getExtensions());
    }

    public function testPrependNamespace(): void
    {
        $this->finder->prependNamespace('test', 'foo');
        $this->finder->prependNamespace('testb', 'baz');
        $this->finder->prependNamespace('test', 'baa');

        self::assertCount(2, $this->finder->getHints());
    }

    public function testPassingViewWithHintReturnsTrue(): void
    {
        self::assertTrue($this->finder->hasHintInformation('hint::foo.bar'));
    }

    public function testPassingViewWithoutHintReturnsFalse(): void
    {
        self::assertFalse($this->finder->hasHintInformation('foo.bar'));
    }

    public function testPassingViewWithFalseHintReturnsFalse(): void
    {
        self::assertFalse($this->finder->hasHintInformation('::foo.bar'));
    }

    public function testPrependLocation(): void
    {
        $this->finder->prependLocation('test');

        self::assertSame(['test', $this->path], $this->finder->getPaths());
    }
}
