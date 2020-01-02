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

namespace Viserio\Component\Finder\Tests;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * @internal
 */
abstract class AbstractRealIteratorTestCase extends AbstractIteratorTestCase
{
    /** @var string */
    protected static $tmpDir;

    /** @var string[] */
    protected static $files;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        self::$tmpDir = static::getTempPath();

        self::$files = [
            '.git/',
            '.foo/',
            '.foo/.bar',
            '.foo/bar',
            '.bar',
            'test.py',
            'foo/',
            'foo/bar.tmp',
            'test.php',
            'toto/',
            'toto/.git/',
            'foo bar',
            'qux_0_1.php',
            'qux_2_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux/',
            'qux/baz_1_2.py',
            'qux/baz_100_1.py',
        ];

        self::$files = (array) self::toAbsolute(self::$files);

        if (\is_dir(self::$tmpDir)) {
            self::tearDownAfterClass();
        } else {
            \mkdir(self::$tmpDir);
        }

        foreach (self::$files as $file) {
            if ($file[\strlen($file) - 1] === \DIRECTORY_SEPARATOR) {
                \mkdir($file);
            } else {
                \touch($file);
            }
        }

        /** @var string $testPhpPath */
        $testPhpPath = self::toAbsolute('test.php');

        /** @var string $testPyPath */
        $testPyPath = self::toAbsolute('test.py');

        \file_put_contents($testPhpPath, \str_repeat(' ', 800));
        \file_put_contents($testPyPath, \str_repeat(' ', 2000));

        /** @var string $gitignorPath */
        $gitignorPath = self::toAbsolute('.gitignore');

        \file_put_contents($gitignorPath, '*.php');

        /** @var string $atimePath */
        $atimePath = self::toAbsolute('atime.php');
        /** @var string $fooBarPath */
        $fooBarPath = self::toAbsolute('foo/bar.tmp');

        /** @noinspection PotentialMalwareInspection */
        \touch($atimePath, \strtotime('2005-10-15'), (int) \date('U'));
        \touch($fooBarPath, \strtotime('2005-10-15'));
        \touch($testPhpPath, \strtotime('2005-10-15'));
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass(): void
    {
        $paths = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::$tmpDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var SplFileInfo $path */
        foreach ($paths as $path) {
            if ($path->isDir()) {
                if ($path->isLink()) {
                    @\unlink($path->getPathname());
                } else {
                    @\rmdir($path->getPathname());
                }
            } else {
                @\unlink($path->getPathname());
            }
        }
    }

    /**
     * @param null|array<int, array<int, string>>|string|string[] $files
     *
     * @return array<mixed>|string
     */
    protected static function toAbsolute($files = null)
    {
        /*
         * Without the call to setUpBeforeClass() property can be null.
         */
        if (self::$tmpDir === null) {
            self::$tmpDir = static::getTempPath();
        }

        if (\is_array($files)) {
            $f = [];

            foreach ($files as $file) {
                if (\is_array($file)) {
                    $f[] = (array) self::toAbsolute($file);
                } else {
                    $f[] = self::$tmpDir . \DIRECTORY_SEPARATOR . \str_replace('/', \DIRECTORY_SEPARATOR, $file);
                }
            }

            return $f;
        }

        if (\is_string($files)) {
            return self::$tmpDir . \DIRECTORY_SEPARATOR . \str_replace('/', \DIRECTORY_SEPARATOR, $files);
        }

        return self::$tmpDir;
    }

    /**
     * @param string[] $files
     *
     * @return array<int, false|string>
     */
    protected static function toAbsoluteFixtures(array $files): array
    {
        $f = [];

        foreach ($files as $file) {
            $f[] = \realpath(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Finder' . \DIRECTORY_SEPARATOR . $file);
        }

        return $f;
    }

    /**
     * @return string
     */
    abstract protected static function getTempPath(): string;
}
