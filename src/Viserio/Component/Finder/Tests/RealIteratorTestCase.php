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
abstract class RealIteratorTestCase extends IteratorTestCase
{
    /** @var string */
    protected static $tmpDir;

    /** @var array */
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

        self::$files = self::toAbsolute(self::$files);

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

        \file_put_contents(self::toAbsolute('test.php'), \str_repeat(' ', 800));
        \file_put_contents(self::toAbsolute('test.py'), \str_repeat(' ', 2000));

        \file_put_contents(self::toAbsolute('.gitignore'), '*.php');

        /** @noinspection PotentialMalwareInspection */
        \touch(self::toAbsolute('atime.php'), \strtotime('2005-10-15'), (int) \date('U'));
        \touch(self::toAbsolute('foo/bar.tmp'), \strtotime('2005-10-15'));
        \touch(self::toAbsolute('test.php'), \strtotime('2005-10-15'));
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
     * @param null|array|string $files
     *
     * @return string|string[]
     */
    protected static function toAbsolute($files = null)
    {
        /*
         * Without the call to setUpBeforeClass() property can be null.
         */
        if (! self::$tmpDir) {
            self::$tmpDir = static::getTempPath();
        }

        if (\is_array($files)) {
            $f = [];

            foreach ($files as $file) {
                if (\is_array($file)) {
                    $f[] = self::toAbsolute($file);
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
     * @param array $files
     *
     * @return array
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
