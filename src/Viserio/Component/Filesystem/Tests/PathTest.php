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

use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Viserio\Component\Filesystem\Path;
use Viserio\Contract\Filesystem\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class PathTest extends TestCase
{
    /** @var array<string, mixed> */
    protected $storedEnv = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->storedEnv['HOME'] = \getenv('HOME');
        $this->storedEnv['HOMEDRIVE'] = \getenv('HOMEDRIVE');
        $this->storedEnv['HOMEPATH'] = \getenv('HOMEPATH');

        \putenv('HOME=/home/filesystem');
        \putenv('HOMEDRIVE=');
        \putenv('HOMEPATH=');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        \putenv('HOME=' . $this->storedEnv['HOME']);
        \putenv('HOMEDRIVE=' . $this->storedEnv['HOMEDRIVE']);
        \putenv('HOMEPATH=' . $this->storedEnv['HOMEPATH']);
    }

    public function provideCanonicalizeCases(): iterable
    {
        // relative paths (forward slash)
        yield ['css/./style.css', 'css/style.css'];

        yield ['css/../style.css', 'style.css'];

        yield ['css/./../style.css', 'style.css'];

        yield ['css/.././style.css', 'style.css'];

        yield ['css/../../style.css', '../style.css'];

        yield ['./css/style.css', 'css/style.css'];

        yield ['../css/style.css', '../css/style.css'];

        yield ['./../css/style.css', '../css/style.css'];

        yield ['.././css/style.css', '../css/style.css'];

        yield ['../../css/style.css', '../../css/style.css'];

        yield ['', ''];

        yield ['.', ''];

        yield ['..', '..'];

        yield ['./..', '..'];

        yield ['../.', '..'];

        yield ['../..', '../..'];
        // relative paths (backslash)
        yield ['css\\.\\style.css', 'css/style.css'];

        yield ['css\\..\\style.css', 'style.css'];

        yield ['css\\.\\..\\style.css', 'style.css'];

        yield ['css\\..\\.\\style.css', 'style.css'];

        yield ['css\\..\\..\\style.css', '../style.css'];

        yield ['.\\css\\style.css', 'css/style.css'];

        yield ['..\\css\\style.css', '../css/style.css'];

        yield ['.\\..\\css\\style.css', '../css/style.css'];

        yield ['..\\.\\css\\style.css', '../css/style.css'];

        yield ['..\\..\\css\\style.css', '../../css/style.css'];
        // absolute paths (forward slash, UNIX)
        yield ['/css/style.css', '/css/style.css'];

        yield ['/css/./style.css', '/css/style.css'];

        yield ['/css/../style.css', '/style.css'];

        yield ['/css/./../style.css', '/style.css'];

        yield ['/css/.././style.css', '/style.css'];

        yield ['/./css/style.css', '/css/style.css'];

        yield ['/../css/style.css', '/css/style.css'];

        yield ['/./../css/style.css', '/css/style.css'];

        yield ['/.././css/style.css', '/css/style.css'];

        yield ['/../../css/style.css', '/css/style.css'];
        // absolute paths (backslash, UNIX)
        yield ['\\css\\style.css', '/css/style.css'];

        yield ['\\css\\.\\style.css', '/css/style.css'];

        yield ['\\css\\..\\style.css', '/style.css'];

        yield ['\\css\\.\\..\\style.css', '/style.css'];

        yield ['\\css\\..\\.\\style.css', '/style.css'];

        yield ['\\.\\css\\style.css', '/css/style.css'];

        yield ['\\..\\css\\style.css', '/css/style.css'];

        yield ['\\.\\..\\css\\style.css', '/css/style.css'];

        yield ['\\..\\.\\css\\style.css', '/css/style.css'];

        yield ['\\..\\..\\css\\style.css', '/css/style.css'];
        // absolute paths (forward slash, Windows)
        yield ['C:/css/style.css', 'C:/css/style.css'];

        yield ['C:/css/./style.css', 'C:/css/style.css'];

        yield ['C:/css/../style.css', 'C:/style.css'];

        yield ['C:/css/./../style.css', 'C:/style.css'];

        yield ['C:/css/.././style.css', 'C:/style.css'];

        yield ['C:/./css/style.css', 'C:/css/style.css'];

        yield ['C:/../css/style.css', 'C:/css/style.css'];

        yield ['C:/./../css/style.css', 'C:/css/style.css'];

        yield ['C:/.././css/style.css', 'C:/css/style.css'];

        yield ['C:/../../css/style.css', 'C:/css/style.css'];
        // absolute paths (backslash, Windows)
        yield ['C:\\css\\style.css', 'C:/css/style.css'];

        yield ['C:\\css\\.\\style.css', 'C:/css/style.css'];

        yield ['C:\\css\\..\\style.css', 'C:/style.css'];

        yield ['C:\\css\\.\\..\\style.css', 'C:/style.css'];

        yield ['C:\\css\\..\\.\\style.css', 'C:/style.css'];

        yield ['C:\\.\\css\\style.css', 'C:/css/style.css'];

        yield ['C:\\..\\css\\style.css', 'C:/css/style.css'];

        yield ['C:\\.\\..\\css\\style.css', 'C:/css/style.css'];

        yield ['C:\\..\\.\\css\\style.css', 'C:/css/style.css'];

        yield ['C:\\..\\..\\css\\style.css', 'C:/css/style.css'];
        // Windows special case
        yield ['C:', 'C:/'];
        // Don't change malformed path
        yield ['C:css/style.css', 'C:css/style.css'];
        // absolute paths (stream, UNIX)
        yield ['phar:///css/style.css', 'phar:///css/style.css'];

        yield ['phar:///css/./style.css', 'phar:///css/style.css'];

        yield ['phar:///css/../style.css', 'phar:///style.css'];

        yield ['phar:///css/./../style.css', 'phar:///style.css'];

        yield ['phar:///css/.././style.css', 'phar:///style.css'];

        yield ['phar:///./css/style.css', 'phar:///css/style.css'];

        yield ['phar:///../css/style.css', 'phar:///css/style.css'];

        yield ['phar:///./../css/style.css', 'phar:///css/style.css'];

        yield ['phar:///.././css/style.css', 'phar:///css/style.css'];

        yield ['phar:///../../css/style.css', 'phar:///css/style.css'];
        // absolute paths (stream, Windows)
        yield ['phar://C:/css/style.css', 'phar://C:/css/style.css'];

        yield ['phar://C:/css/./style.css', 'phar://C:/css/style.css'];

        yield ['phar://C:/css/../style.css', 'phar://C:/style.css'];

        yield ['phar://C:/css/./../style.css', 'phar://C:/style.css'];

        yield ['phar://C:/css/.././style.css', 'phar://C:/style.css'];

        yield ['phar://C:/./css/style.css', 'phar://C:/css/style.css'];

        yield ['phar://C:/../css/style.css', 'phar://C:/css/style.css'];

        yield ['phar://C:/./../css/style.css', 'phar://C:/css/style.css'];

        yield ['phar://C:/.././css/style.css', 'phar://C:/css/style.css'];

        yield ['phar://C:/../../css/style.css', 'phar://C:/css/style.css'];
    }

    public function provideCanonicalizeWithHomeForUnixCases(): iterable
    {
        // paths with "~" UNIX
        yield ['~/css/style.css', '/home/filesystem/css/style.css'];

        yield ['~webmozart/css/style.css', '/home/webmozart/css/style.css'];

        yield ['~/css/./style.css', '/home/filesystem/css/style.css'];

        yield ['~/css/../style.css', '/home/filesystem/style.css'];

        yield ['~/css/./../style.css', '/home/filesystem/style.css'];

        yield ['~/css/.././style.css', '/home/filesystem/style.css'];

        yield ['~/./css/style.css', '/home/filesystem/css/style.css'];

        yield ['~/../css/style.css', '/home/css/style.css'];

        yield ['~/./../css/style.css', '/home/css/style.css'];

        yield ['~/.././css/style.css', '/home/css/style.css'];

        yield ['~/../../css/style.css', '/css/style.css'];
    }

    /**
     * @dataProvider provideCanonicalizeCases
     *
     * @param string $path
     * @param string $canonicalized
     */
    public function testCanonicalize(string $path, string $canonicalized): void
    {
        self::assertSame($canonicalized, Path::canonicalize($path));
    }

    public function provideGetDirectoryCases(): iterable
    {
        yield ['/filesystem/path/style.css', '/filesystem/path'];

        yield ['/filesystem/path', '/filesystem'];

        yield ['/filesystem', '/'];

        yield ['/', '/'];

        yield ['', ''];

        yield ['\\filesystem\\path\\style.css', '/filesystem/path'];

        yield ['\\filesystem\\path', '/filesystem'];

        yield ['\\filesystem', '/'];

        yield ['\\', '/'];

        yield ['C:/filesystem/path/style.css', 'C:/filesystem/path'];

        yield ['C:/filesystem/path', 'C:/filesystem'];

        yield ['C:/filesystem', 'C:/'];

        yield ['C:/', 'C:/'];

        yield ['C:', 'C:/'];

        yield ['C:\\filesystem\\path\\style.css', 'C:/filesystem/path'];

        yield ['C:\\filesystem\\path', 'C:/filesystem'];

        yield ['C:\\filesystem', 'C:/'];

        yield ['C:\\', 'C:/'];

        yield ['phar:///filesystem/path/style.css', 'phar:///filesystem/path'];

        yield ['phar:///filesystem/path', 'phar:///filesystem'];

        yield ['phar:///filesystem', 'phar:///'];

        yield ['phar:///', 'phar:///'];

        yield ['phar://C:/filesystem/path/style.css', 'phar://C:/filesystem/path'];

        yield ['phar://C:/filesystem/path', 'phar://C:/filesystem'];

        yield ['phar://C:/filesystem', 'phar://C:/'];

        yield ['phar://C:/', 'phar://C:/'];

        yield ['filesystem/path/style.css', 'filesystem/path'];

        yield ['filesystem/path', 'filesystem'];

        yield ['filesystem', ''];

        yield ['filesystem\\path\\style.css', 'filesystem/path'];

        yield ['filesystem\\path', 'filesystem'];

        yield ['filesystem', ''];

        yield ['/filesystem/./path/style.css', '/filesystem/path'];

        yield ['/filesystem/../path/style.css', '/path'];

        yield ['/filesystem/./../path/style.css', '/path'];

        yield ['/filesystem/.././path/style.css', '/path'];

        yield ['/filesystem/../../path/style.css', '/path'];

        yield ['/.', '/'];

        yield ['/..', '/'];

        yield ['C:filesystem', ''];
    }

    /**
     * @dataProvider provideGetDirectoryCases
     *
     * @param string $path
     * @param string $directory
     */
    public function testGetDirectory(string $path, string $directory): void
    {
        self::assertSame($directory, Path::getDirectory($path));
    }

    public function provideGetFilenameWithoutExtensionCases(): iterable
    {
        yield ['/filesystem/path/style.css.twig', null, 'style.css'];

        yield ['/filesystem/path/style.css.', null, 'style.css'];

        yield ['/filesystem/path/style.css', null, 'style'];

        yield ['/filesystem/path/.style.css', null, '.style'];

        yield ['/filesystem/path/', null, 'path'];

        yield ['/filesystem/path', null, 'path'];

        yield ['/', null, ''];

        yield ['', null, ''];

        yield ['/filesystem/path/style.css', 'css', 'style'];

        yield ['/filesystem/path/style.css', '.css', 'style'];

        yield ['/filesystem/path/style.css', 'twig', 'style.css'];

        yield ['/filesystem/path/style.css', '.twig', 'style.css'];

        yield ['/filesystem/path/style.css', '', 'style.css'];

        yield ['/filesystem/path/style.css.', '', 'style.css'];

        yield ['/filesystem/path/style.css.', '.', 'style.css'];

        yield ['/filesystem/path/style.css.', '.css', 'style.css'];

        yield ['/filesystem/path/.style.css', 'css', '.style'];

        yield ['/filesystem/path/.style.css', '.css', '.style'];
    }

    /**
     * @dataProvider provideGetFilenameWithoutExtensionCases
     *
     * @param string  $path
     * @param ?string $extension
     * @param string  $filename
     */
    public function testGetFilenameWithoutExtension(string $path, ?string $extension, string $filename): void
    {
        self::assertSame($filename, Path::getFilenameWithoutExtension($path, $extension));
    }

    public function provideGetExtensionCases(): iterable
    {
        yield ['/filesystem/path/style.css.twig', false, 'twig'];

        yield ['/filesystem/path/style.css', false, 'css'];

        yield ['/filesystem/path/style.css.', false, ''];

        yield ['/filesystem/path/', false, ''];

        yield ['/filesystem/path', false, ''];

        yield ['/', false, ''];

        yield ['', false, ''];

        yield ['/filesystem/path/style.CSS', false, 'CSS'];

        yield ['/filesystem/path/style.CSS', true, 'css'];

        yield ['/filesystem/path/style.ÄÖÜ', false, 'ÄÖÜ'];

        yield ['/filesystem/path/style.ÄÖÜ', true, 'äöü'];
    }

    /**
     * @dataProvider provideGetExtensionCases
     *
     * @param string $path
     * @param bool   $forceLowerCase
     * @param string $extension
     */
    public function testGetExtension(string $path, bool $forceLowerCase, string $extension): void
    {
        self::assertSame($extension, Path::getExtension($path, $forceLowerCase));
    }

    public function provideHasExtensionCases(): iterable
    {
        yield [true, '/filesystem/path/style.css.twig', null, false];

        yield [true, '/filesystem/path/style.css', null, false];

        yield [false, '/filesystem/path/style.css.', null, false];

        yield [false, '/filesystem/path/', null, false];

        yield [false, '/filesystem/path', null, false];

        yield [false, '/', null, false];

        yield [false, '', null, false];

        yield [true, '/filesystem/path/style.css.twig', 'twig', false];

        yield [false, '/filesystem/path/style.css.twig', 'css', false];

        yield [true, '/filesystem/path/style.css', 'css', false];

        yield [true, '/filesystem/path/style.css', '.css', false];

        yield [true, '/filesystem/path/style.css.', '', false];

        yield [false, '/filesystem/path/', 'ext', false];

        yield [false, '/filesystem/path', 'ext', false];

        yield [false, '/', 'ext', false];

        yield [false, '', 'ext', false];

        yield [false, '/filesystem/path/style.css', 'CSS', false];

        yield [true, '/filesystem/path/style.css', 'CSS', true];

        yield [false, '/filesystem/path/style.CSS', 'css', false];

        yield [true, '/filesystem/path/style.CSS', 'css', true];

        yield [true, '/filesystem/path/style.ÄÖÜ', 'ÄÖÜ', false];

        yield [true, '/filesystem/path/style.css', ['ext', 'css'], false];

        yield [true, '/filesystem/path/style.css', ['.ext', '.css'], false];

        yield [true, '/filesystem/path/style.css.', ['ext', ''], false];

        yield [false, '/filesystem/path/style.css', ['foo', 'bar', ''], false];

        yield [false, '/filesystem/path/style.css', ['.foo', '.bar', ''], false];
        // This can only be tested, when mbstring is installed
        yield [true, '/filesystem/path/style.ÄÖÜ', 'äöü', true];

        yield [true, '/filesystem/path/style.ÄÖÜ', ['äöü'], true];
    }

    /**
     * @dataProvider provideHasExtensionCases
     *
     * @param bool                 $hasExtension
     * @param string               $path
     * @param null|string|string[] $extension
     * @param bool                 $ignoreCase
     */
    public function testHasExtension(bool $hasExtension, string $path, $extension, bool $ignoreCase): void
    {
        self::assertSame($hasExtension, Path::hasExtension($path, $extension, $ignoreCase));
    }

    public function provideChangeExtensionCases(): iterable
    {
        yield ['/filesystem/path/style.css.twig', 'html', '/filesystem/path/style.css.html'];

        yield ['/filesystem/path/style.css', 'sass', '/filesystem/path/style.sass'];

        yield ['/filesystem/path/style.css', '.sass', '/filesystem/path/style.sass'];

        yield ['/filesystem/path/style.css', '', '/filesystem/path/style.'];

        yield ['/filesystem/path/style.css.', 'twig', '/filesystem/path/style.css.twig'];

        yield ['/filesystem/path/style.css.', '', '/filesystem/path/style.css.'];

        yield ['/filesystem/path/style.css', 'äöü', '/filesystem/path/style.äöü'];

        yield ['/filesystem/path/style.äöü', 'css', '/filesystem/path/style.css'];

        yield ['/filesystem/path/', 'css', '/filesystem/path/'];

        yield ['/filesystem/path', 'css', '/filesystem/path.css'];

        yield ['/', 'css', '/'];

        yield ['', 'css', ''];
    }

    /**
     * @dataProvider provideChangeExtensionCases
     *
     * @param string $path
     * @param string $extension
     * @param string $pathExpected
     */
    public function testChangeExtension(string $path, string $extension, string $pathExpected): void
    {
        self::assertSame($pathExpected, Path::changeExtension($path, $extension));
    }

    public function provideIsAbsolutePathTests(): iterable
    {
        yield ['/css/style.css', true];

        yield ['/', true];

        yield ['css/style.css', false];

        yield ['', false];

        yield ['\\css\\style.css', true];

        yield ['\\', true];

        yield ['css\\style.css', false];

        yield ['C:/css/style.css', true];

        yield ['D:/', true];

        yield ['E:\\css\\style.css', true];

        yield ['F:\\', true];

        yield ['phar:///css/style.css', true];

        yield ['phar:///', true];
        // Windows special case
        yield ['C:', true];
        // Not considered absolute
        yield ['C:css/style.css', false];
    }

    /**
     * @dataProvider provideIsAbsolutePathTests
     *
     * @param string $path
     * @param bool   $isAbsolute
     */
    public function testIsAbsolute(string $path, bool $isAbsolute): void
    {
        self::assertSame($isAbsolute, Path::isAbsolute($path));
    }

    /**
     * @dataProvider provideIsAbsolutePathTests
     *
     * @param string $path
     * @param bool   $isAbsolute
     */
    public function testIsRelative(string $path, bool $isAbsolute): void
    {
        self::assertSame(! $isAbsolute, Path::isRelative($path));
    }

    public function provideGetRootCases(): iterable
    {
        yield ['/css/style.css', '/'];

        yield ['/', '/'];

        yield ['css/style.css', ''];

        yield ['', ''];

        yield ['\\css\\style.css', '/'];

        yield ['\\', '/'];

        yield ['css\\style.css', ''];

        yield ['C:/css/style.css', 'C:/'];

        yield ['C:/', 'C:/'];

        yield ['C:', 'C:/'];

        yield ['D:\\css\\style.css', 'D:/'];

        yield ['D:\\', 'D:/'];

        yield ['phar:///css/style.css', 'phar:///'];

        yield ['phar:///', 'phar:///'];

        yield ['phar://C:/css/style.css', 'phar://C:/'];

        yield ['phar://C:/', 'phar://C:/'];

        yield ['phar://C:', 'phar://C:/'];
    }

    /**
     * @dataProvider provideGetRootCases
     *
     * @param string $path
     * @param string $root
     */
    public function testGetRoot(string $path, string $root): void
    {
        self::assertSame($root, Path::getRoot($path));
    }

    public function providePathTests(): Generator
    {
        // relative to absolute path
        yield ['css/style.css', '/filesystem/path', '/filesystem/path/css/style.css'];

        yield ['../css/style.css', '/filesystem/path', '/filesystem/css/style.css'];

        yield ['../../css/style.css', '/filesystem/path', '/css/style.css'];
        // relative to root
        yield ['css/style.css', '/', '/css/style.css'];

        yield ['css/style.css', 'C:', 'C:/css/style.css'];

        yield ['css/style.css', 'C:/', 'C:/css/style.css'];
        // same sub directories in different base directories
        yield ['../../path/css/style.css', '/filesystem/css', '/path/css/style.css'];

        yield ['', '/filesystem/path', '/filesystem/path'];

        yield ['..', '/filesystem/path', '/filesystem'];
    }

    public function provideMakeAbsoluteCases(): iterable
    {
        foreach ($this->providePathTests() as $set) {
            yield $set;
        }
        // collapse dots
        yield ['css/./style.css', '/filesystem/path', '/filesystem/path/css/style.css'];

        yield ['css/../style.css', '/filesystem/path', '/filesystem/path/style.css'];

        yield ['css/./../style.css', '/filesystem/path', '/filesystem/path/style.css'];

        yield ['css/.././style.css', '/filesystem/path', '/filesystem/path/style.css'];

        yield ['./css/style.css', '/filesystem/path', '/filesystem/path/css/style.css'];

        yield ['css\\.\\style.css', '\\filesystem\\path', '/filesystem/path/css/style.css'];

        yield ['css\\..\\style.css', '\\filesystem\\path', '/filesystem/path/style.css'];

        yield ['css\\.\\..\\style.css', '\\filesystem\\path', '/filesystem/path/style.css'];

        yield ['css\\..\\.\\style.css', '\\filesystem\\path', '/filesystem/path/style.css'];

        yield ['.\\css\\style.css', '\\filesystem\\path', '/filesystem/path/css/style.css'];
        // collapse dots on root
        yield ['./css/style.css', '/', '/css/style.css'];

        yield ['../css/style.css', '/', '/css/style.css'];

        yield ['../css/./style.css', '/', '/css/style.css'];

        yield ['../css/../style.css', '/', '/style.css'];

        yield ['../css/./../style.css', '/', '/style.css'];

        yield ['../css/.././style.css', '/', '/style.css'];

        yield ['.\\css\\style.css', '\\', '/css/style.css'];

        yield ['..\\css\\style.css', '\\', '/css/style.css'];

        yield ['..\\css\\.\\style.css', '\\', '/css/style.css'];

        yield ['..\\css\\..\\style.css', '\\', '/style.css'];

        yield ['..\\css\\.\\..\\style.css', '\\', '/style.css'];

        yield ['..\\css\\..\\.\\style.css', '\\', '/style.css'];

        yield ['./css/style.css', 'C:/', 'C:/css/style.css'];

        yield ['../css/style.css', 'C:/', 'C:/css/style.css'];

        yield ['../css/./style.css', 'C:/', 'C:/css/style.css'];

        yield ['../css/../style.css', 'C:/', 'C:/style.css'];

        yield ['../css/./../style.css', 'C:/', 'C:/style.css'];

        yield ['../css/.././style.css', 'C:/', 'C:/style.css'];

        yield ['.\\css\\style.css', 'C:\\', 'C:/css/style.css'];

        yield ['..\\css\\style.css', 'C:\\', 'C:/css/style.css'];

        yield ['..\\css\\.\\style.css', 'C:\\', 'C:/css/style.css'];

        yield ['..\\css\\..\\style.css', 'C:\\', 'C:/style.css'];

        yield ['..\\css\\.\\..\\style.css', 'C:\\', 'C:/style.css'];

        yield ['..\\css\\..\\.\\style.css', 'C:\\', 'C:/style.css'];

        yield ['./css/style.css', 'phar:///', 'phar:///css/style.css'];

        yield ['../css/style.css', 'phar:///', 'phar:///css/style.css'];

        yield ['../css/./style.css', 'phar:///', 'phar:///css/style.css'];

        yield ['../css/../style.css', 'phar:///', 'phar:///style.css'];

        yield ['../css/./../style.css', 'phar:///', 'phar:///style.css'];

        yield ['../css/.././style.css', 'phar:///', 'phar:///style.css'];

        yield ['./css/style.css', 'phar://C:/', 'phar://C:/css/style.css'];

        yield ['../css/style.css', 'phar://C:/', 'phar://C:/css/style.css'];

        yield ['../css/./style.css', 'phar://C:/', 'phar://C:/css/style.css'];

        yield ['../css/../style.css', 'phar://C:/', 'phar://C:/style.css'];

        yield ['../css/./../style.css', 'phar://C:/', 'phar://C:/style.css'];

        yield ['../css/.././style.css', 'phar://C:/', 'phar://C:/style.css'];
        // absolute paths
        yield ['/css/style.css', '/filesystem/path', '/css/style.css'];

        yield ['\\css\\style.css', '/filesystem/path', '/css/style.css'];

        yield ['C:/css/style.css', 'C:/filesystem/path', 'C:/css/style.css'];

        yield ['D:\\css\\style.css', 'D:/filesystem/path', 'D:/css/style.css'];
    }

    /**
     * @dataProvider provideMakeAbsoluteCases
     *
     * @param string $relativePath
     * @param string $basePath
     * @param string $absolutePath
     */
    public function testMakeAbsolute(string $relativePath, string $basePath, string $absolutePath): void
    {
        self::assertSame($absolutePath, Path::makeAbsolute($relativePath, $basePath));
    }

    public function testMakeAbsoluteFailsIfBasePathNotAbsolute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The base path [filesystem/path] is not an absolute path.');

        Path::makeAbsolute('css/style.css', 'filesystem/path');
    }

    public function testMakeAbsoluteFailsIfBasePathEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The base path must be a non-empty string. Got: []');
        Path::makeAbsolute('css/style.css', '');
    }

    public function provideAbsolutePathsWithDifferentRoots(): iterable
    {
        yield ['C:/css/style.css', '/filesystem/path'];

        yield ['C:/css/style.css', '\\filesystem\\path'];

        yield ['C:\\css\\style.css', '/filesystem/path'];

        yield ['C:\\css\\style.css', '\\filesystem\\path'];

        yield ['/css/style.css', 'C:/filesystem/path'];

        yield ['/css/style.css', 'C:\\filesystem\\path'];

        yield ['\\css\\style.css', 'C:/filesystem/path'];

        yield ['\\css\\style.css', 'C:\\filesystem\\path'];

        yield ['D:/css/style.css', 'C:/filesystem/path'];

        yield ['D:/css/style.css', 'C:\\filesystem\\path'];

        yield ['D:\\css\\style.css', 'C:/filesystem/path'];

        yield ['D:\\css\\style.css', 'C:\\filesystem\\path'];

        yield ['phar:///css/style.css', '/filesystem/path'];

        yield ['/css/style.css', 'phar:///filesystem/path'];

        yield ['phar://C:/css/style.css', 'C:/filesystem/path'];

        yield ['phar://C:/css/style.css', 'C:\\filesystem\\path'];

        yield ['phar://C:\\css\\style.css', 'C:/filesystem/path'];

        yield ['phar://C:\\css\\style.css', 'C:\\filesystem\\path'];
    }

    /**
     * @dataProvider provideAbsolutePathsWithDifferentRoots
     *
     * @param string $basePath
     * @param string $absolutePath
     */
    public function testMakeAbsoluteDoesNotFailIfDifferentRoot(string $basePath, string $absolutePath): void
    {
        // If a path in partition D: is passed, but $basePath is in partition
        // C:, the path should be returned unchanged
        self::assertSame(Path::canonicalize($absolutePath), Path::makeAbsolute($absolutePath, $basePath));
    }

    public function provideMakeRelativeCases(): iterable
    {
        foreach ($this->providePathTests() as $set) {
            yield [$set[2], $set[1], $set[0]];
        }

        yield ['/filesystem/path/./css/style.css', '/filesystem/path', 'css/style.css'];

        yield ['/filesystem/path/../css/style.css', '/filesystem/path', '../css/style.css'];

        yield ['/filesystem/path/.././css/style.css', '/filesystem/path', '../css/style.css'];

        yield ['/filesystem/path/./../css/style.css', '/filesystem/path', '../css/style.css'];

        yield ['/filesystem/path/../../css/style.css', '/filesystem/path', '../../css/style.css'];

        yield ['/filesystem/path/css/style.css', '/filesystem/./path', 'css/style.css'];

        yield ['/filesystem/path/css/style.css', '/filesystem/../path', '../filesystem/path/css/style.css'];

        yield ['/filesystem/path/css/style.css', '/filesystem/./../path', '../filesystem/path/css/style.css'];

        yield ['/filesystem/path/css/style.css', '/filesystem/.././path', '../filesystem/path/css/style.css'];

        yield ['/filesystem/path/css/style.css', '/filesystem/../../path', '../filesystem/path/css/style.css'];
        // first argument shorter than second
        yield ['/css', '/filesystem/path', '../../css'];
        // second argument shorter than first
        yield ['/filesystem/path', '/css', '../filesystem/path'];

        yield ['\\filesystem\\path\\css\\style.css', '\\filesystem\\path', 'css/style.css'];

        yield ['\\filesystem\\css\\style.css', '\\filesystem\\path', '../css/style.css'];

        yield ['\\css\\style.css', '\\filesystem\\path', '../../css/style.css'];

        yield ['C:/filesystem/path/css/style.css', 'C:/filesystem/path', 'css/style.css'];

        yield ['C:/filesystem/css/style.css', 'C:/filesystem/path', '../css/style.css'];

        yield ['C:/css/style.css', 'C:/filesystem/path', '../../css/style.css'];

        yield ['C:\\filesystem\\path\\css\\style.css', 'C:\\filesystem\\path', 'css/style.css'];

        yield ['C:\\filesystem\\css\\style.css', 'C:\\filesystem\\path', '../css/style.css'];

        yield ['C:\\css\\style.css', 'C:\\filesystem\\path', '../../css/style.css'];

        yield ['phar:///filesystem/path/css/style.css', 'phar:///filesystem/path', 'css/style.css'];

        yield ['phar:///filesystem/css/style.css', 'phar:///filesystem/path', '../css/style.css'];

        yield ['phar:///css/style.css', 'phar:///filesystem/path', '../../css/style.css'];

        yield ['phar://C:/filesystem/path/css/style.css', 'phar://C:/filesystem/path', 'css/style.css'];

        yield ['phar://C:/filesystem/css/style.css', 'phar://C:/filesystem/path', '../css/style.css'];

        yield ['phar://C:/css/style.css', 'phar://C:/filesystem/path', '../../css/style.css'];
        // already relative + already in root basepath
        yield ['../style.css', '/', 'style.css'];

        yield ['./style.css', '/', 'style.css'];

        yield ['../../style.css', '/', 'style.css'];

        yield ['..\\style.css', 'C:\\', 'style.css'];

        yield ['.\\style.css', 'C:\\', 'style.css'];

        yield ['..\\..\\style.css', 'C:\\', 'style.css'];

        yield ['../style.css', 'C:/', 'style.css'];

        yield ['./style.css', 'C:/', 'style.css'];

        yield ['../../style.css', 'C:/', 'style.css'];

        yield ['..\\style.css', '\\', 'style.css'];

        yield ['.\\style.css', '\\', 'style.css'];

        yield ['..\\..\\style.css', '\\', 'style.css'];

        yield ['../style.css', 'phar:///', 'style.css'];

        yield ['./style.css', 'phar:///', 'style.css'];

        yield ['../../style.css', 'phar:///', 'style.css'];

        yield ['..\\style.css', 'phar://C:\\', 'style.css'];

        yield ['.\\style.css', 'phar://C:\\', 'style.css'];

        yield ['..\\..\\style.css', 'phar://C:\\', 'style.css'];

        yield ['css/../style.css', '/', 'style.css'];

        yield ['css/./style.css', '/', 'css/style.css'];

        yield ['css\\..\\style.css', 'C:\\', 'style.css'];

        yield ['css\\.\\style.css', 'C:\\', 'css/style.css'];

        yield ['css/../style.css', 'C:/', 'style.css'];

        yield ['css/./style.css', 'C:/', 'css/style.css'];

        yield ['css\\..\\style.css', '\\', 'style.css'];

        yield ['css\\.\\style.css', '\\', 'css/style.css'];

        yield ['css/../style.css', 'phar:///', 'style.css'];

        yield ['css/./style.css', 'phar:///', 'css/style.css'];

        yield ['css\\..\\style.css', 'phar://C:\\', 'style.css'];

        yield ['css\\.\\style.css', 'phar://C:\\', 'css/style.css'];
        // already relative
        yield ['css/style.css', '/filesystem/path', 'css/style.css'];

        yield ['css\\style.css', '\\filesystem\\path', 'css/style.css'];
        // both relative
        yield ['css/style.css', 'filesystem/path', '../../css/style.css'];

        yield ['css\\style.css', 'filesystem\\path', '../../css/style.css'];
        // relative to empty
        yield ['css/style.css', '', 'css/style.css'];

        yield ['css\\style.css', '', 'css/style.css'];
        // different slashes in path and base path
        yield ['/filesystem/path/css/style.css', '\\filesystem\\path', 'css/style.css'];

        yield ['\\filesystem\\path\\css\\style.css', '/filesystem/path', 'css/style.css'];
    }

    /**
     * @dataProvider provideMakeRelativeCases
     *
     * @param string $absolutePath
     * @param string $basePath
     * @param string $relativePath
     */
    public function testMakeRelative(string $absolutePath, string $basePath, string $relativePath): void
    {
        self::assertSame($relativePath, Path::makeRelative($absolutePath, $basePath));
    }

    public function testMakeRelativeFailsIfAbsolutePathAndBasePathNotAbsolute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The absolute path [/filesystem/path/css/style.css] cannot be made relative to the relative path [filesystem/path]. You should provide an absolute base path instead.');
        Path::makeRelative('/filesystem/path/css/style.css', 'filesystem/path');
    }

    public function testMakeRelativeFailsIfAbsolutePathAndBasePathEmpty(): void
    {
        $this->expectExceptionMessage('The absolute path [/filesystem/path/css/style.css] cannot be made relative to the relative path []. You should provide an absolute base path instead.');
        Path::makeRelative('/filesystem/path/css/style.css', '');
    }

    /**
     * @dataProvider provideAbsolutePathsWithDifferentRoots
     *
     * @param string $absolutePath
     * @param string $basePath
     */
    public function testMakeRelativeFailsIfDifferentRoot(string $absolutePath, string $basePath): void
    {
        $this->expectException(InvalidArgumentException::class);
        Path::makeRelative($absolutePath, $basePath);
    }

    public function provideIsLocalCases(): iterable
    {
        yield ['/bg.png', true];

        yield ['bg.png', true];

        yield ['http://example.com/bg.png', false];

        yield ['http://example.com', false];

        yield ['', false];
    }

    /**
     * @dataProvider provideIsLocalCases
     *
     * @param string $path
     * @param bool   $isLocal
     */
    public function testIsLocal(string $path, bool $isLocal): void
    {
        self::assertSame($isLocal, Path::isLocal($path));
    }

    public function provideGetLongestCommonBasePathCases(): iterable
    {
        // same paths
        yield [['/base/path', '/base/path'], '/base/path'];

        yield [['C:/base/path', 'C:/base/path'], 'C:/base/path'];

        yield [['C:\\base\\path', 'C:\\base\\path'], 'C:/base/path'];

        yield [['C:/base/path', 'C:\\base\\path'], 'C:/base/path'];

        yield [['phar:///base/path', 'phar:///base/path'], 'phar:///base/path'];

        yield [['phar://C:/base/path', 'phar://C:/base/path'], 'phar://C:/base/path'];
        // trailing slash
        yield [['/base/path/', '/base/path'], '/base/path'];

        yield [['C:/base/path/', 'C:/base/path'], 'C:/base/path'];

        yield [['C:\\base\\path\\', 'C:\\base\\path'], 'C:/base/path'];

        yield [['C:/base/path/', 'C:\\base\\path'], 'C:/base/path'];

        yield [['phar:///base/path/', 'phar:///base/path'], 'phar:///base/path'];

        yield [['phar://C:/base/path/', 'phar://C:/base/path'], 'phar://C:/base/path'];

        yield [['/base/path', '/base/path/'], '/base/path'];

        yield [['C:/base/path', 'C:/base/path/'], 'C:/base/path'];

        yield [['C:\\base\\path', 'C:\\base\\path\\'], 'C:/base/path'];

        yield [['C:/base/path', 'C:\\base\\path\\'], 'C:/base/path'];

        yield [['phar:///base/path', 'phar:///base/path/'], 'phar:///base/path'];

        yield [['phar://C:/base/path', 'phar://C:/base/path/'], 'phar://C:/base/path'];
        // first in second
        yield [['/base/path/sub', '/base/path'], '/base/path'];

        yield [['C:/base/path/sub', 'C:/base/path'], 'C:/base/path'];

        yield [['C:\\base\\path\\sub', 'C:\\base\\path'], 'C:/base/path'];

        yield [['C:/base/path/sub', 'C:\\base\\path'], 'C:/base/path'];

        yield [['phar:///base/path/sub', 'phar:///base/path'], 'phar:///base/path'];

        yield [['phar://C:/base/path/sub', 'phar://C:/base/path'], 'phar://C:/base/path'];
        // second in first
        yield [['/base/path', '/base/path/sub'], '/base/path'];

        yield [['C:/base/path', 'C:/base/path/sub'], 'C:/base/path'];

        yield [['C:\\base\\path', 'C:\\base\\path\\sub'], 'C:/base/path'];

        yield [['C:/base/path', 'C:\\base\\path\\sub'], 'C:/base/path'];

        yield [['phar:///base/path', 'phar:///base/path/sub'], 'phar:///base/path'];

        yield [['phar://C:/base/path', 'phar://C:/base/path/sub'], 'phar://C:/base/path'];
        // first is prefix
        yield [['/base/path/di', '/base/path/dir'], '/base/path'];

        yield [['C:/base/path/di', 'C:/base/path/dir'], 'C:/base/path'];

        yield [['C:\\base\\path\\di', 'C:\\base\\path\\dir'], 'C:/base/path'];

        yield [['C:/base/path/di', 'C:\\base\\path\\dir'], 'C:/base/path'];

        yield [['phar:///base/path/di', 'phar:///base/path/dir'], 'phar:///base/path'];

        yield [['phar://C:/base/path/di', 'phar://C:/base/path/dir'], 'phar://C:/base/path'];
        // second is prefix
        yield [['/base/path/dir', '/base/path/di'], '/base/path'];

        yield [['C:/base/path/dir', 'C:/base/path/di'], 'C:/base/path'];

        yield [['C:\\base\\path\\dir', 'C:\\base\\path\\di'], 'C:/base/path'];

        yield [['C:/base/path/dir', 'C:\\base\\path\\di'], 'C:/base/path'];

        yield [['phar:///base/path/dir', 'phar:///base/path/di'], 'phar:///base/path'];

        yield [['phar://C:/base/path/dir', 'phar://C:/base/path/di'], 'phar://C:/base/path'];
        // root is common base path
        yield [['/first', '/second'], '/'];

        yield [['C:/first', 'C:/second'], 'C:/'];

        yield [['C:\\first', 'C:\\second'], 'C:/'];

        yield [['C:/first', 'C:\\second'], 'C:/'];

        yield [['phar:///first', 'phar:///second'], 'phar:///'];

        yield [['phar://C:/first', 'phar://C:/second'], 'phar://C:/'];
        // windows vs unix
        yield [['/base/path', 'C:/base/path'], null];

        yield [['C:/base/path', '/base/path'], null];

        yield [['/base/path', 'C:\\base\\path'], null];

        yield [['phar:///base/path', 'phar://C:/base/path'], null];
        // different partitions
        yield [['C:/base/path', 'D:/base/path'], null];

        yield [['C:/base/path', 'D:\\base\\path'], null];

        yield [['C:\\base\\path', 'D:\\base\\path'], null];

        yield [['phar://C:/base/path', 'phar://D:/base/path'], null];
        // three paths
        yield [['/base/path/foo', '/base/path', '/base/path/bar'], '/base/path'];

        yield [['C:/base/path/foo', 'C:/base/path', 'C:/base/path/bar'], 'C:/base/path'];

        yield [['C:\\base\\path\\foo', 'C:\\base\\path', 'C:\\base\\path\\bar'], 'C:/base/path'];

        yield [['C:/base/path//foo', 'C:/base/path', 'C:\\base\\path\\bar'], 'C:/base/path'];

        yield [['phar:///base/path/foo', 'phar:///base/path', 'phar:///base/path/bar'], 'phar:///base/path'];

        yield [['phar://C:/base/path/foo', 'phar://C:/base/path', 'phar://C:/base/path/bar'], 'phar://C:/base/path'];
        // three paths with root
        yield [['/base/path/foo', '/', '/base/path/bar'], '/'];

        yield [['C:/base/path/foo', 'C:/', 'C:/base/path/bar'], 'C:/'];

        yield [['C:\\base\\path\\foo', 'C:\\', 'C:\\base\\path\\bar'], 'C:/'];

        yield [['C:/base/path//foo', 'C:/', 'C:\\base\\path\\bar'], 'C:/'];

        yield [['phar:///base/path/foo', 'phar:///', 'phar:///base/path/bar'], 'phar:///'];

        yield [['phar://C:/base/path/foo', 'phar://C:/', 'phar://C:/base/path/bar'], 'phar://C:/'];
        // three paths, different roots
        yield [['/base/path/foo', 'C:/base/path', '/base/path/bar'], null];

        yield [['/base/path/foo', 'C:\\base\\path', '/base/path/bar'], null];

        yield [['C:/base/path/foo', 'D:/base/path', 'C:/base/path/bar'], null];

        yield [['C:\\base\\path\\foo', 'D:\\base\\path', 'C:\\base\\path\\bar'], null];

        yield [['C:/base/path//foo', 'D:/base/path', 'C:\\base\\path\\bar'], null];

        yield [['phar:///base/path/foo', 'phar://C:/base/path', 'phar:///base/path/bar'], null];

        yield [['phar://C:/base/path/foo', 'phar://D:/base/path', 'phar://C:/base/path/bar'], null];
        // only one path
        yield [['/base/path'], '/base/path'];

        yield [['C:/base/path'], 'C:/base/path'];

        yield [['C:\\base\\path'], 'C:/base/path'];

        yield [['phar:///base/path'], 'phar:///base/path'];

        yield [['phar://C:/base/path'], 'phar://C:/base/path'];
    }

    /**
     * @dataProvider provideGetLongestCommonBasePathCases
     *
     * @param string[] $paths
     * @param ?string  $basePath
     */
    public function testGetLongestCommonBasePath(array $paths, ?string $basePath): void
    {
        self::assertSame($basePath, Path::getLongestCommonBasePath(...$paths));
    }

    public function provideIsBasePathCases(): iterable
    {
        // same paths
        yield ['/base/path', '/base/path', true];

        yield ['C:/base/path', 'C:/base/path', true];

        yield ['C:\\base\\path', 'C:\\base\\path', true];

        yield ['C:/base/path', 'C:\\base\\path', true];

        yield ['phar:///base/path', 'phar:///base/path', true];

        yield ['phar://C:/base/path', 'phar://C:/base/path', true];
        // trailing slash
        yield ['/base/path/', '/base/path', true];

        yield ['C:/base/path/', 'C:/base/path', true];

        yield ['C:\\base\\path\\', 'C:\\base\\path', true];

        yield ['C:/base/path/', 'C:\\base\\path', true];

        yield ['phar:///base/path/', 'phar:///base/path', true];

        yield ['phar://C:/base/path/', 'phar://C:/base/path', true];

        yield ['/base/path', '/base/path/', true];

        yield ['C:/base/path', 'C:/base/path/', true];

        yield ['C:\\base\\path', 'C:\\base\\path\\', true];

        yield ['C:/base/path', 'C:\\base\\path\\', true];

        yield ['phar:///base/path', 'phar:///base/path/', true];

        yield ['phar://C:/base/path', 'phar://C:/base/path/', true];
        // first in second
        yield ['/base/path/sub', '/base/path', false];

        yield ['C:/base/path/sub', 'C:/base/path', false];

        yield ['C:\\base\\path\\sub', 'C:\\base\\path', false];

        yield ['C:/base/path/sub', 'C:\\base\\path', false];

        yield ['phar:///base/path/sub', 'phar:///base/path', false];

        yield ['phar://C:/base/path/sub', 'phar://C:/base/path', false];
        // second in first
        yield ['/base/path', '/base/path/sub', true];

        yield ['C:/base/path', 'C:/base/path/sub', true];

        yield ['C:\\base\\path', 'C:\\base\\path\\sub', true];

        yield ['C:/base/path', 'C:\\base\\path\\sub', true];

        yield ['phar:///base/path', 'phar:///base/path/sub', true];

        yield ['phar://C:/base/path', 'phar://C:/base/path/sub', true];
        // first is prefix
        yield ['/base/path/di', '/base/path/dir', false];

        yield ['C:/base/path/di', 'C:/base/path/dir', false];

        yield ['C:\\base\\path\\di', 'C:\\base\\path\\dir', false];

        yield ['C:/base/path/di', 'C:\\base\\path\\dir', false];

        yield ['phar:///base/path/di', 'phar:///base/path/dir', false];

        yield ['phar://C:/base/path/di', 'phar://C:/base/path/dir', false];
        // second is prefix
        yield ['/base/path/dir', '/base/path/di', false];

        yield ['C:/base/path/dir', 'C:/base/path/di', false];

        yield ['C:\\base\\path\\dir', 'C:\\base\\path\\di', false];

        yield ['C:/base/path/dir', 'C:\\base\\path\\di', false];

        yield ['phar:///base/path/dir', 'phar:///base/path/di', false];

        yield ['phar://C:/base/path/dir', 'phar://C:/base/path/di', false];
        // root
        yield ['/', '/second', true];

        yield ['C:/', 'C:/second', true];

        yield ['C:', 'C:/second', true];

        yield ['C:\\', 'C:\\second', true];

        yield ['C:/', 'C:\\second', true];

        yield ['phar:///', 'phar:///second', true];

        yield ['phar://C:/', 'phar://C:/second', true];
        // windows vs unix
        yield ['/base/path', 'C:/base/path', false];

        yield ['C:/base/path', '/base/path', false];

        yield ['/base/path', 'C:\\base\\path', false];

        yield ['/base/path', 'phar:///base/path', false];

        yield ['phar:///base/path', 'phar://C:/base/path', false];
        // different partitions
        yield ['C:/base/path', 'D:/base/path', false];

        yield ['C:/base/path', 'D:\\base\\path', false];

        yield ['C:\\base\\path', 'D:\\base\\path', false];

        yield ['C:/base/path', 'phar://C:/base/path', false];

        yield ['phar://C:/base/path', 'phar://D:/base/path', false];
    }

    /**
     * @dataProvider provideIsBasePathCases
     *
     * @param string $path
     * @param string $ofPath
     * @param bool   $result
     */
    public function testIsBasePath(string $path, string $ofPath, bool $result): void
    {
        self::assertSame($result, Path::isBasePath($path, $ofPath));
    }

    public function provideJoinCases(): iterable
    {
        yield [['', ''], ''];

        yield [['/path/to/test', ''], '/path/to/test'];

        yield [['/path/to//test', ''], '/path/to/test'];

        yield [['', '/path/to/test'], '/path/to/test'];

        yield [['', '/path/to//test'], '/path/to/test'];

        yield [['/path/to/test', 'subdir'], '/path/to/test/subdir'];

        yield [['/path/to/test/', 'subdir'], '/path/to/test/subdir'];

        yield [['/path/to/test', '/subdir'], '/path/to/test/subdir'];

        yield [['/path/to/test/', '/subdir'], '/path/to/test/subdir'];

        yield [['/path/to/test', './subdir'], '/path/to/test/subdir'];

        yield [['/path/to/test/', './subdir'], '/path/to/test/subdir'];

        yield [['/path/to/test/', '../parentdir'], '/path/to/parentdir'];

        yield [['/path/to/test', '../parentdir'], '/path/to/parentdir'];

        yield [['path/to/test/', '/subdir'], 'path/to/test/subdir'];

        yield [['path/to/test', '/subdir'], 'path/to/test/subdir'];

        yield [['../path/to/test', '/subdir'], '../path/to/test/subdir'];

        yield [['path', '../../subdir'], '../subdir'];

        yield [['/path', '../../subdir'], '/subdir'];

        yield [['../path', '../../subdir'], '../../subdir'];

        yield [['/path/to/test', 'subdir', ''], '/path/to/test/subdir'];

        yield [['/path/to/test', '/subdir', ''], '/path/to/test/subdir'];

        yield [['/path/to/test/', 'subdir', ''], '/path/to/test/subdir'];

        yield [['/path/to/test/', '/subdir', ''], '/path/to/test/subdir'];

        yield [['/path', ''], '/path'];

        yield [['/path', 'to', '/test', ''], '/path/to/test'];

        yield [['/path', '', '/test', ''], '/path/test'];

        yield [['path', 'to', 'test', ''], 'path/to/test'];

        yield [[], ''];

        yield [['base/path', 'to/test'], 'base/path/to/test'];

        yield [['C:\\path\\to\\test', 'subdir'], 'C:/path/to/test/subdir'];

        yield [['C:\\path\\to\\test\\', 'subdir'], 'C:/path/to/test/subdir'];

        yield [['C:\\path\\to\\test', '/subdir'], 'C:/path/to/test/subdir'];

        yield [['C:\\path\\to\\test\\', '/subdir'], 'C:/path/to/test/subdir'];

        yield [['/', 'subdir'], '/subdir'];

        yield [['/', '/subdir'], '/subdir'];

        yield [['C:/', 'subdir'], 'C:/subdir'];

        yield [['C:/', '/subdir'], 'C:/subdir'];

        yield [['C:\\', 'subdir'], 'C:/subdir'];

        yield [['C:\\', '/subdir'], 'C:/subdir'];

        yield [['C:', 'subdir'], 'C:/subdir'];

        yield [['C:', '/subdir'], 'C:/subdir'];

        yield [['phar://', '/path/to/test'], 'phar:///path/to/test'];

        yield [['phar:///', '/path/to/test'], 'phar:///path/to/test'];

        yield [['phar:///path/to/test', 'subdir'], 'phar:///path/to/test/subdir'];

        yield [['phar:///path/to/test', 'subdir/'], 'phar:///path/to/test/subdir'];

        yield [['phar:///path/to/test', '/subdir'], 'phar:///path/to/test/subdir'];

        yield [['phar:///path/to/test/', 'subdir'], 'phar:///path/to/test/subdir'];

        yield [['phar:///path/to/test/', '/subdir'], 'phar:///path/to/test/subdir'];

        yield [['phar://', 'C:/path/to/test'], 'phar://C:/path/to/test'];

        yield [['phar://', 'C:\\path\\to\\test'], 'phar://C:/path/to/test'];

        yield [['phar://C:/path/to/test', 'subdir'], 'phar://C:/path/to/test/subdir'];

        yield [['phar://C:/path/to/test', 'subdir/'], 'phar://C:/path/to/test/subdir'];

        yield [['phar://C:/path/to/test', '/subdir'], 'phar://C:/path/to/test/subdir'];

        yield [['phar://C:/path/to/test/', 'subdir'], 'phar://C:/path/to/test/subdir'];

        yield [['phar://C:/path/to/test/', '/subdir'], 'phar://C:/path/to/test/subdir'];

        yield [['phar://C:', 'path/to/test'], 'phar://C:/path/to/test'];

        yield [['phar://C:', '/path/to/test'], 'phar://C:/path/to/test'];

        yield [['phar://C:/', 'path/to/test'], 'phar://C:/path/to/test'];

        yield [['phar://C:/', '/path/to/test'], 'phar://C:/path/to/test'];
    }

    /**
     * @dataProvider provideJoinCases
     *
     * @param array $paths
     * @param mixed $result
     */
    public function testJoin(array $paths, $result): void
    {
        self::assertSame($result, Path::join(...$paths));
    }

    public function testJoinVarArgs(): void
    {
        self::assertSame('/path', Path::join('/path'));
        self::assertSame('/path/to', Path::join('/path', 'to'));
        self::assertSame('/path/to/test', Path::join('/path', 'to', '/test'));
        self::assertSame('/path/to/test/subdir', Path::join('/path', 'to', '/test', 'subdir/'));
    }

    public function testGetHomeDirectoryFailsIfNotSupportedOperationSystem(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Your environment or operation system isn\'t supported');

        \putenv('HOME=');

        Path::getHomeDirectory();
    }

    public function testGetHomeDirectoryForUnix(): void
    {
        self::assertEquals('/home/filesystem', Path::getHomeDirectory());
        self::assertEquals('/home/foo', Path::getHomeDirectory('foo'));
    }

    public function testGetHomeDirectoryForWindows(): void
    {
        \putenv('HOME=');
        \putenv('HOMEDRIVE=C:');
        \putenv('HOMEPATH=/users/filesystem');

        self::assertEquals('C:/users/filesystem', Path::getHomeDirectory());
        self::assertEquals('C:/users/foo', Path::getHomeDirectory('foo'));
    }

    public function testNormalize(): void
    {
        self::assertSame('C:/Foo/Bar/test', Path::normalize('C:\\Foo\\Bar/test'));
    }

    public function provideCanonicalizeWithHomeForWindowsCases(): iterable
    {
        // paths with "~" Windows
        yield ['~/css/style.css', 'C:/users/webmozart/css/style.css'];

        yield ['~filesystem/css/style.css', 'C:/users/filesystem/css/style.css'];

        yield ['~/css/./style.css', 'C:/users/webmozart/css/style.css'];

        yield ['~/css/../style.css', 'C:/users/webmozart/style.css'];

        yield ['~/css/./../style.css', 'C:/users/webmozart/style.css'];

        yield ['~/css/.././style.css', 'C:/users/webmozart/style.css'];

        yield ['~/./css/style.css', 'C:/users/webmozart/css/style.css'];

        yield ['~/../css/style.css', 'C:/users/css/style.css'];

        yield ['~/./../css/style.css', 'C:/users/css/style.css'];

        yield ['~/.././css/style.css', 'C:/users/css/style.css'];

        yield ['~/../../css/style.css', 'C:/css/style.css'];
    }

    /**
     * @dataProvider provideCanonicalizeWithHomeForUnixCases
     *
     * @param string $path
     * @param string $canonicalized
     */
    public function testCanonicalizeWithHomeForUnix(string $path, string $canonicalized): void
    {
        self::assertSame($canonicalized, Path::canonicalize($path));
    }

    /**
     * @dataProvider provideCanonicalizeWithHomeForWindowsCases
     *
     * @param mixed $path
     * @param mixed $canonicalized
     */
    public function testCanonicalizeWithHomeForWindows($path, $canonicalized): void
    {
        \putenv('HOME=');
        \putenv('HOMEDRIVE=C:');
        \putenv('HOMEPATH=/users/webmozart');
        self::assertSame($canonicalized, Path::canonicalize($path));
    }
}
