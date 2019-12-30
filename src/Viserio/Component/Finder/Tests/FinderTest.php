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

use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use Viserio\Component\Filesystem\Path;
use Viserio\Component\Finder\Comparator\DateComparator;
use Viserio\Component\Finder\Finder;
use Viserio\Component\Finder\SplFileInfo;
use Viserio\Contract\Finder\Exception\AccessDeniedException;
use Viserio\Contract\Finder\Exception\LogicException;
use Viserio\Contract\Finder\Exception\NotFoundException;

/**
 * @internal
 *
 * @small
 */
final class FinderTest extends RealIteratorTestCase
{
    /** @var string */
    private $fixturePath;

    /** @var Finder */
    private $finder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturePath = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Finder';
        $this->finder = Finder::create();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->finder = null;
    }

    public function testDirectories(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->directories());

        $this->assertIterator(self::toAbsolute(['foo', 'qux', 'toto']), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();
        $finder->directories();
        $finder->files();
        $finder->directories();

        $this->assertIterator(self::toAbsolute(['foo', 'qux', 'toto']), $finder->in(self::$tmpDir)->getIterator());
    }

    public function testFiles(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->files());

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'foo bar',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        $finder->files();
        $finder->directories();
        $finder->files();

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'foo bar',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());
    }

    public function testRemoveTrailingSlash(): void
    {
        $expected = self::toAbsolute([
            'atime.php',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'foo bar',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]);

        $in = self::$tmpDir . '//';

        $this->assertIterator($expected, (new Finder())->in($in)->files()->getIterator());
    }

    public function testSymlinksNotResolved(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('symlinks are not supported on Windows');
        }


        \symlink(self::toAbsolute('foo'), self::toAbsolute('baz'));

        $expected = self::toAbsolute(['baz/bar.tmp']);
        $in = self::$tmpDir . '/baz/';

        try {
            $this->assertIterator($expected, $this->finder->in($in)->files()->getIterator());
            \unlink(self::toAbsolute('baz'));
        } catch (Exception $e) {
            \unlink(self::toAbsolute('baz'));

            throw $e;
        }
    }

    public function testBackPathNotNormalized(): void
    {
        $expected = self::toAbsolute(['foo/../foo/bar.tmp']);
        $in = self::$tmpDir . '/foo/../foo/';

        $this->assertIterator($expected, $this->finder->in($in)->files()->getIterator());
    }

    public function testDepth(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->depth('< 1'));

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'test.php',
            'test.py',
            'toto',
            'foo bar',
            'qux',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        self::assertSame($finder, $finder->depth('<= 0'));

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'test.php',
            'test.py',
            'toto',
            'foo bar',
            'qux',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        self::assertSame($finder, $finder->depth('>= 1'));

        $this->assertIterator(self::toAbsolute([
            'foo/bar.tmp',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder->depth('< 1')->depth('>= 1');

        $this->assertIterator([], $finder->in(self::$tmpDir)->getIterator());
    }

    public function testDepthWithArrayParam(): void
    {
        $this->finder->depth(['>= 1', '< 2']);

        $this->assertIterator(self::toAbsolute([
            'foo/bar.tmp',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testName(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->name('*.php'));
        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'test.php',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();
        $finder->name('test.ph*');
        $finder->name('test.py');

        $this->assertIterator(self::toAbsolute(['test.php', 'test.py']), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();
        $finder->name('~^test~i');

        $this->assertIterator(self::toAbsolute(['test.php', 'test.py']), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();
        $finder->name('~\\.php$~i');

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'test.php',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();
        $finder->name('test.p{hp,y}');

        $this->assertIterator(self::toAbsolute(['test.php', 'test.py']), $finder->in(self::$tmpDir)->getIterator());
    }

    public function testNameWithArrayParam(): void
    {
        $this->finder->name(['test.php', 'test.py']);

        $this->assertIterator(self::toAbsolute(['test.php', 'test.py']), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testNotName(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->notName('*.php'));
        $this->assertIterator(self::toAbsolute([
            'foo',
            'foo/bar.tmp',
            'test.py',
            'toto',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        $finder->notName('*.php');
        $finder->notName('*.py');

        $this->assertIterator(self::toAbsolute([
            'foo',
            'foo/bar.tmp',
            'toto',
            'foo bar',
            'qux',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        $finder->name('test.ph*');
        $finder->name('test.py');
        $finder->notName('*.php');
        $finder->notName('*.py');

        $this->assertIterator([], $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        $finder->name('test.ph*');
        $finder->name('test.py');
        $finder->notName('*.p{hp,y}');

        $this->assertIterator([], $finder->in(self::$tmpDir)->getIterator());
    }

    public function testNotNameWithArrayParam(): void
    {
        $this->finder->notName(['*.php', '*.py']);

        $this->assertIterator(self::toAbsolute([
            'foo',
            'foo/bar.tmp',
            'toto',
            'foo bar',
            'qux',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    /**
     * @dataProvider provideRegexNameCases
     *
     * @param string          $expected
     * @param string|string[] $regex
     *
     * @return void
     */
    public function testRegexName($expected, $regex): void
    {
        $finder = new Finder();
        $finder->name($regex);

        $this->assertIterator(self::toAbsolute($expected), $finder->in(self::$tmpDir)->getIterator());
    }

    public function testSize(): void
    {
        self::assertSame($this->finder, $this->finder->files()->size('< 1K')->size('> 500'));

        $this->assertIterator(self::toAbsolute(['test.php']), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testSizeWithArrayParam(): void
    {
        self::assertSame($this->finder, $this->finder->files()->size(['< 1K', '> 500']));

        $this->assertIterator(self::toAbsolute(['test.php']), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testDate(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->files()->date('until last month'));
        $this->assertIterator(self::toAbsolute(['atime.php', 'foo/bar.tmp', 'test.php']), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        self::assertSame($finder, $finder->files()->date('until last month', DateComparator::LAST_ACCESSED));
        $this->assertIterator(self::toAbsolute(['foo/bar.tmp', 'test.php']), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        self::assertSame($finder, $finder->files()->date('until last month', DateComparator::LAST_CHANGED));
        $this->assertIterator(self::toAbsolute([]), $finder->in(self::$tmpDir)->getIterator());
    }

    public function testDateWithArrayParam(): void
    {
        self::assertSame($this->finder, $this->finder->files()->date(['>= 2005-10-15', 'until last month']));

        $this->assertIterator(self::toAbsolute(['atime.php', 'foo/bar.tmp', 'test.php']), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testExclude(): void
    {
        self::assertSame($this->finder, $this->finder->exclude('foo'));

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'test.php',
            'test.py',
            'toto',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testIgnoreVCS(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->ignoreVCS(false)->ignoreDotFiles(false));
        $this->assertIterator(self::toAbsolute([
            'atime.php',
            '.gitignore',
            '.git',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
            'toto/.git',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();
        $finder->ignoreVCS(false)->ignoreVCS(false)->ignoreDotFiles(false);

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            '.gitignore',
            '.git',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
            'toto/.git',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());

        self::assertSame($this->finder, $this->finder->ignoreVCS(true)->ignoreDotFiles(false));

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            '.gitignore',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testIgnoreVCSIgnored(): void
    {
        self::assertSame(
            $this->finder,
            $this->finder
                ->ignoreVCS(true)
                ->ignoreDotFiles(true)
                ->ignoreVCSIgnored(true)
        );

        $this->assertIterator(self::toAbsolute([
            'foo',
            'foo/bar.tmp',
            'test.py',
            'toto',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testIgnoreVCSCanBeDisabledAfterFirstIteration(): void
    {
        $finder = new Finder();
        $finder->in(self::$tmpDir);
        $finder->ignoreDotFiles(false);

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            '.gitignore',
            'foo',
            'foo/bar.tmp',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
            'toto',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
        ]), $finder->getIterator());

        $finder->ignoreVCS(false);

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            '.gitignore',
            '.git',
            'foo',
            'foo/bar.tmp',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
            'toto',
            'toto/.git',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
        ]), $finder->getIterator());
    }

    public function testIgnoreDotFiles(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->ignoreDotFiles(false)->ignoreVCS(false));
        $this->assertIterator(self::toAbsolute([
            'atime.php',
            '.gitignore',
            '.git',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
            'toto/.git',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();
        $finder->ignoreDotFiles(false)->ignoreDotFiles(false)->ignoreVCS(false);

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            '.gitignore',
            '.git',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
            'toto/.git',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        self::assertSame($finder, $finder->ignoreDotFiles(true)->ignoreVCS(false));
        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());
    }

    public function testIgnoreDotFilesCanBeDisabledAfterFirstIteration(): void
    {
        $finder = new Finder();
        $finder->in(self::$tmpDir);

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'foo/bar.tmp',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
            'toto',
            'foo bar',
        ]), $finder->getIterator());

        $finder->ignoreDotFiles(false);

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            '.gitignore',
            'foo',
            'foo/bar.tmp',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
            'toto',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
        ]), $finder->getIterator());
    }

    public function testSortByName(): void
    {
        self::assertSame($this->finder, $this->finder->sortByName());

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'foo bar',
            'foo/bar.tmp',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
            'toto',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testSortByType(): void
    {
        self::assertSame($this->finder, $this->finder->sortByType());

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'foo bar',
            'toto',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testSortByAccessedTime(): void
    {
        self::assertSame($this->finder, $this->finder->sortByAccessedTime());

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo/bar.tmp',
            'test.php',
            'toto',
            'test.py',
            'foo',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testSortByChangedTime(): void
    {
        self::assertSame($this->finder, $this->finder->sortByChangedTime());

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'toto',
            'test.py',
            'test.php',
            'foo/bar.tmp',
            'foo',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testSortByModifiedTime(): void
    {
        self::assertSame($this->finder, $this->finder->sortByModifiedTime());

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo/bar.tmp',
            'test.php',
            'toto',
            'test.py',
            'foo',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testReverseSorting(): void
    {
        self::assertSame($this->finder, $this->finder->sortByName());
        self::assertSame($this->finder, $this->finder->reverseSorting());

        $this->assertOrderedIteratorInForeach(self::toAbsolute([
            'toto',
            'test.py',
            'test.php',
            'qux_2_0.php',
            'qux_12_0.php',
            'qux_10_2.php',
            'qux_1002_0.php',
            'qux_1000_1.php',
            'qux_0_1.php',
            'qux/baz_1_2.py',
            'qux/baz_100_1.py',
            'qux',
            'foo/bar.tmp',
            'foo bar',
            'foo',
            'atime.php',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testSortByNameNatural(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->sortByName(true));
        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'foo bar',
            'foo/bar.tmp',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
            'toto',
        ]), $finder->in(self::$tmpDir)->getIterator());

        $finder = new Finder();

        self::assertSame($finder, $finder->sortByName(false));
        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'foo bar',
            'foo/bar.tmp',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
            'toto',
        ]), $finder->in(self::$tmpDir)->getIterator());
    }

    public function testSort(): void
    {
        $finder = new Finder();

        self::assertSame($finder, $finder->sort(static function (SplFileInfo $a, SplFileInfo $b) {
            return \strcmp($a->getRealPath(), $b->getRealPath());
        }));

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'foo bar',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $finder->in(self::$tmpDir)->getIterator());
    }

    public function testFilter(): void
    {
        self::assertSame($this->finder, $this->finder->filter(static function (SplFileInfo $f) {
            return \strpos($f->getPathname(), 'test') !== false;
        }));

        $this->assertIterator(self::toAbsolute(['test.php', 'test.py']), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testFollowLinks(): void
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            self::markTestSkipped('symlinks are not supported on Windows');
        }

        self::assertSame($this->finder, $this->finder->followLinks());

        $this->assertIterator(self::toAbsolute([
            'atime.php',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'test.py',
            'toto',
            'foo bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ]), $this->finder->in(self::$tmpDir)->getIterator());
    }

    public function testIn(): void
    {
        $finder = new Finder();

        $iterator = $finder->files()
            ->name('*.php')
            ->depth('< 1')
            ->in([self::$tmpDir, __DIR__])
            ->getIterator();

        $expected = [
            self::$tmpDir . \DIRECTORY_SEPARATOR . 'atime.php',
            self::$tmpDir . \DIRECTORY_SEPARATOR . 'test.php',
            __DIR__ . \DIRECTORY_SEPARATOR . 'FileInfoTest.php',
            __DIR__ . \DIRECTORY_SEPARATOR . 'HelperTest.php',
            __DIR__ . \DIRECTORY_SEPARATOR . 'GitignoreTest.php',
            __DIR__ . \DIRECTORY_SEPARATOR . 'FinderTest.php',
            __DIR__ . \DIRECTORY_SEPARATOR . 'IteratorTestCase.php',
            __DIR__ . \DIRECTORY_SEPARATOR . 'RealIteratorTestCase.php',
            __DIR__ . \DIRECTORY_SEPARATOR . 'TestStreamWrapper.php',
            __DIR__ . \DIRECTORY_SEPARATOR . 'UtilTest.php',
            self::$tmpDir . \DIRECTORY_SEPARATOR . 'qux_0_1.php',
            self::$tmpDir . \DIRECTORY_SEPARATOR . 'qux_1000_1.php',
            self::$tmpDir . \DIRECTORY_SEPARATOR . 'qux_1002_0.php',
            self::$tmpDir . \DIRECTORY_SEPARATOR . 'qux_10_2.php',
            self::$tmpDir . \DIRECTORY_SEPARATOR . 'qux_12_0.php',
            self::$tmpDir . \DIRECTORY_SEPARATOR . 'qux_2_0.php',
        ];

        $this->assertIterator($expected, $iterator);
    }

    public function testInWithNonExistentDirectory(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessageRegExp('/The \[.*\] directory does not exist\./');

        $finder = new Finder();
        $finder->in(__DIR__ . \DIRECTORY_SEPARATOR . 'foobar');
    }

    public function testInWithGlob(): void
    {
        $this->finder->in([$this->fixturePath . \DIRECTORY_SEPARATOR . '*/B/C/', $this->fixturePath . \DIRECTORY_SEPARATOR . '*/*/B/C/'])->getIterator();

        $this->assertIterator(self::toAbsoluteFixtures(['A/B/C/abc.dat', 'copy/A/B/C/abc.dat.copy']), $this->finder);
    }

    public function testInWithNonDirectoryGlob(): void
    {
        $this->expectException(NotFoundException::class);

        $finder = new Finder();
        $finder->in($this->fixturePath . \DIRECTORY_SEPARATOR . 'A/a*');
    }

    public function testInWithGlobBrace(): void
    {
        if (! \defined('GLOB_BRACE')) {
            self::markTestSkipped('Glob brace is not supported on this system.');
        }

        $this->finder->in([$this->fixturePath . \DIRECTORY_SEPARATOR . '{A,copy/A}/B/C'])->getIterator();

        $this->assertIterator(self::toAbsoluteFixtures(['A/B/C/abc.dat', 'copy/A/B/C/abc.dat.copy']), $this->finder);
    }

    public function testGetIteratorWithoutIn(): void
    {
        $this->expectException(LogicException::class);

        $finder = Finder::create();
        $finder->getIterator();
    }

    public function testGetIterator(): void
    {
        $finder = new Finder();

        $dirs = [];

        foreach ($finder->directories()->in(self::$tmpDir) as $dir) {
            $dirs[] = (string) $dir;
        }

        $expected = self::toAbsolute(['foo', 'qux', 'toto']);

        \sort($dirs);
        \sort($expected);

        self::assertEquals($expected, $dirs, 'implements the \IteratorAggregate interface');

        $finder = new Finder();

        self::assertEquals(3, \iterator_count($finder->directories()->in(self::$tmpDir)), 'implements the \IteratorAggregate interface');

        $finder = new Finder();

        $a = \iterator_to_array($finder->directories()->in(self::$tmpDir));
        $a = \array_values(\array_map('\strval', $a));

        \sort($a);

        self::assertEquals($expected, $a, 'implements the \IteratorAggregate interface');
    }

    public function testRelativePath(): void
    {
        $finder = $this->finder->in(self::$tmpDir);

        $paths = [];

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $paths[] = $file->getRelativePath();
        }

        $relativePath = Path::makeRelative(self::$tmpDir, \getcwd());

        $ref = [$relativePath, $relativePath, $relativePath, $relativePath, $relativePath, $relativePath, $relativePath, $relativePath, $relativePath, $relativePath, $relativePath, $relativePath, $relativePath . '/foo', $relativePath . '/qux', $relativePath . '/qux', $relativePath];

        \sort($ref);
        \sort($paths);

        self::assertEquals($ref, $paths);
    }

    public function testRelativePathname(): void
    {
        $finder = $this->finder->in(self::$tmpDir)->sortByName();

        $paths = [];

        foreach ($finder as $file) {
            $paths[] = $file->getRelativePathname();
        }

        $relativePath = Path::makeRelative(self::$tmpDir, \getcwd()) . \DIRECTORY_SEPARATOR;

        $ref = [
            $relativePath . 'test.php',
            $relativePath . 'toto',
            $relativePath . 'test.py',
            $relativePath . 'foo',
            $relativePath . 'foo' . \DIRECTORY_SEPARATOR . 'bar.tmp',
            $relativePath . 'foo bar',
            $relativePath . 'atime.php',
            $relativePath . 'qux',
            $relativePath . 'qux' . \DIRECTORY_SEPARATOR . 'baz_100_1.py',
            $relativePath . 'qux' . \DIRECTORY_SEPARATOR . 'baz_1_2.py',
            $relativePath . 'qux_0_1.php',
            $relativePath . 'qux_1000_1.php',
            $relativePath . 'qux_1002_0.php',
            $relativePath . 'qux_10_2.php',
            $relativePath . 'qux_12_0.php',
            $relativePath . 'qux_2_0.php',
        ];

        \sort($paths);
        \sort($ref);

        self::assertEquals($ref, $paths);
    }

    public function testGetFilenameWithoutExtension(): void
    {
        $finder = (new Finder())->in(self::$tmpDir)->sortByName();

        $fileNames = [];

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $fileNames[] = $file->getFilenameWithoutExtension();
        }

        $ref = [
            'atime',
            'test',
            'toto',
            'test',
            'foo',
            'bar',
            'foo bar',
            'qux',
            'baz_100_1',
            'baz_1_2',
            'qux_0_1',
            'qux_1000_1',
            'qux_1002_0',
            'qux_10_2',
            'qux_12_0',
            'qux_2_0',
        ];

        \sort($fileNames);
        \sort($ref);

        self::assertEquals($ref, $fileNames);
    }

    public function testAppendWithAFinder(): void
    {
        $finder = new Finder();

        $finder->files()->in(self::$tmpDir . \DIRECTORY_SEPARATOR . 'foo');

        $finder1 = new Finder();
        $finder1->directories()->in(self::$tmpDir);

        $finder = $finder->append($finder1);

        $this->assertIterator(self::toAbsolute(['foo', 'foo/bar.tmp', 'qux', 'toto']), $finder->getIterator());
    }

    public function testAppendWithAnArray(): void
    {
        $this->finder->files()->in(self::$tmpDir . \DIRECTORY_SEPARATOR . 'foo');

        $this->finder->append(self::toAbsolute(['foo', 'toto']));

        $this->assertIterator(self::toAbsolute(['foo', 'foo/bar.tmp', 'toto']), $this->finder->getIterator());
    }

    public function testAppendDoesNotRequireIn(): void
    {
        $this->finder->in(self::$tmpDir . \DIRECTORY_SEPARATOR . 'foo');

        $finder1 = Finder::create()->append($this->finder);

        $this->assertIterator(\iterator_to_array($this->finder->getIterator()), $finder1->getIterator());
    }

    public function testCountDirectories(): void
    {
        $directory = Finder::create()->directories()->in(self::$tmpDir);
        $i = 0;

        foreach ($directory as $dir) {
            $i++;
        }

        self::assertCount($i, $directory);
    }

    public function testCountFiles(): void
    {
        $files = Finder::create()->files()->in($this->fixturePath);
        $i = 0;

        foreach ($files as $file) {
            $i++;
        }

        self::assertCount($i, $files);
    }

    public function testCountWithoutIn(): void
    {
        $this->expectException(LogicException::class);

        $finder = Finder::create()->files();

        \count($finder);
    }

    public function testHasResults(): void
    {
        $this->finder->in(__DIR__);

        self::assertTrue($this->finder->hasResults());
    }

    public function testNoResults(): void
    {
        $this->finder->in(__DIR__)->name('DoesNotExist');

        self::assertFalse($this->finder->hasResults());
    }

    /**
     * @dataProvider provideContainsCases
     *
     * @param mixed $matchPatterns
     * @param mixed $noMatchPatterns
     * @param mixed $expected
     */
    public function testContains($matchPatterns, $noMatchPatterns, $expected): void
    {
        $this->finder->in($this->fixturePath)
            ->name('*.txt')->sortByName()
            ->contains($matchPatterns)
            ->notContains($noMatchPatterns);

        $this->assertIterator(self::toAbsoluteFixtures($expected), $this->finder);
    }

    public function testContainsOnDirectory(): void
    {
        $this->finder->in(__DIR__)
            ->directories()
            ->name('Fixtures')
            ->contains('abc');

        $this->assertIterator([], $this->finder);
    }

    public function testNotContainsOnDirectory(): void
    {
        $this->finder->in(__DIR__)
            ->directories()
            ->name('Fixtures')
            ->notContains('abc');

        $this->assertIterator([], $this->finder);
    }

    /**
     * Searching in multiple locations involves AppendIterator which does an unnecessary rewind which leaves FilterIterator
     * with inner FilesystemIterator in an invalid state.
     *
     * @see https://bugs.php.net/68557
     */
    public function testMultipleLocations(): void
    {
        $locations = [
            self::$tmpDir . '/',
            self::$tmpDir . '/toto/',
        ];

        // it is expected that there are test.py test.php in the tmpDir
        $finder = new Finder();
        $this->finder->in($locations)
            // the default flag IGNORE_DOT_FILES fixes the problem indirectly
            // so we set it to false for better isolation
            ->ignoreDotFiles(false)
            ->depth('< 1')->name('test.php');

        self::assertCount(1, $this->finder);
    }

    /**
     * Searching in multiple locations with sub directories involves
     * AppendIterator which does an unnecessary rewind which leaves
     * FilterIterator with inner FilesystemIterator in an invalid state.
     *
     * @see https://bugs.php.net/68557
     */
    public function testMultipleLocationsWithSubDirectories(): void
    {
        $locations = [
            $this->fixturePath . \DIRECTORY_SEPARATOR . 'one',
            self::$tmpDir . \DIRECTORY_SEPARATOR . 'toto',
        ];

        $this->finder->in($locations)->depth('< 10')->name('*.neon');

        $expected = [
            $this->fixturePath . \DIRECTORY_SEPARATOR . 'one' . \DIRECTORY_SEPARATOR . 'b' . \DIRECTORY_SEPARATOR . 'c.neon',
            $this->fixturePath . \DIRECTORY_SEPARATOR . 'one' . \DIRECTORY_SEPARATOR . 'b' . \DIRECTORY_SEPARATOR . 'd.neon',
        ];

        $this->assertIterator($expected, $this->finder);
        $this->assertIteratorInForeach($expected, $this->finder);
    }

    /**
     * Iterator keys must be the file pathname.
     */
    public function testIteratorKeys(): void
    {
        $finder = $this->finder->in(self::$tmpDir);

        foreach ($finder as $key => $file) {
            self::assertEquals($file->getPathname(), $key);
        }
    }

    public function testRegexSpecialCharsLocationWithPathRestrictionContainingStartFlag(): void
    {
        $this->finder->in($this->fixturePath . \DIRECTORY_SEPARATOR . 'r+e.gex[c]a(r)s')
            ->path('/^dir/');

        $expected = ['r+e.gex[c]a(r)s' . \DIRECTORY_SEPARATOR . 'dir', 'r+e.gex[c]a(r)s' . \DIRECTORY_SEPARATOR . 'dir' . \DIRECTORY_SEPARATOR . 'bar.dat'];

        $this->assertIterator(self::toAbsoluteFixtures($expected), $this->finder);
    }

    /**
     * @return iterable
     */
    public function provideContainsCases(): iterable
    {
        yield ['', '', []];

        yield ['foo', 'bar', []];

        yield ['', 'foobar', ['dolor.txt', 'ipsum.txt', 'lorem.txt']];

        yield ['lorem ipsum dolor sit amet', 'foobar', ['lorem.txt']];

        yield ['sit', 'bar', ['dolor.txt', 'ipsum.txt', 'lorem.txt']];

        yield ['dolor sit amet', '@^L@m', ['dolor.txt', 'ipsum.txt']];

        yield ['/^lorem ipsum dolor sit amet$/m', 'foobar', ['lorem.txt']];

        yield ['lorem', 'foobar', ['lorem.txt']];

        yield ['', 'lorem', ['dolor.txt', 'ipsum.txt']];

        yield ['ipsum dolor sit amet', '/^IPSUM/m', ['lorem.txt']];

        yield [['lorem', 'dolor'], [], ['lorem.txt', 'ipsum.txt', 'dolor.txt']];

        yield ['', ['lorem', 'ipsum'], ['dolor.txt']];
    }

    /**
     * @return iterable
     */
    public function provideRegexNameCases(): iterable
    {
        yield [['test.php', 'test.py'], '~.*t\\.p.+~i'];

        yield [['test.py', 'test.php'], '~t.*s~i'];
    }

    /**
     * @dataProvider providePathCases
     *
     * @param mixed $matchPatterns
     * @param mixed $noMatchPatterns
     * @param array $expected
     */
    public function testPath($matchPatterns, $noMatchPatterns, array $expected): void
    {
        $this->finder->in($this->fixturePath)
            ->path($matchPatterns)
            ->notPath($noMatchPatterns);

        $this->assertIterator(self::toAbsoluteFixtures($expected), $this->finder);
    }

    /**
     * @return iterable
     */
    public function providePathCases(): iterable
    {
        yield ['', '', []];

        yield ['/^A\/B\/C/', '/C$/',
            ['A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C' . \DIRECTORY_SEPARATOR . 'abc.dat'],
        ];

        yield ['/^A\/B/', 'foobar',
            [
                'A' . \DIRECTORY_SEPARATOR . 'B',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'ab.dat',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C' . \DIRECTORY_SEPARATOR . 'abc.dat',
            ],
        ];

        yield ['A/B/C', 'foobar',
            [
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C' . \DIRECTORY_SEPARATOR . 'abc.dat',
            ],
        ];

        yield ['A/B', 'foobar',
            [
                // dirs
                'A' . \DIRECTORY_SEPARATOR . 'B',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C',
                // files
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'ab.dat',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C' . \DIRECTORY_SEPARATOR . 'abc.dat',
            ],
        ];

        yield ['/^with space\//', 'foobar',
            [
                'with space' . \DIRECTORY_SEPARATOR . 'foo.txt',
            ],
        ];

        yield [
            '/^A/',
            ['A/a.dat', 'A/B/C/abc.dat'],
            [
                'A',
                'A' . \DIRECTORY_SEPARATOR . 'B',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'ab.dat',
            ],
        ];

        yield [
            ['/^A/', 'one'],
            'foobar',
            [
                'A',
                'A' . \DIRECTORY_SEPARATOR . 'B',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C',
                'A' . \DIRECTORY_SEPARATOR . 'a.dat',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'ab.dat',
                'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C' . \DIRECTORY_SEPARATOR . 'abc.dat',
                'one',
                'one' . \DIRECTORY_SEPARATOR . 'a',
                'one' . \DIRECTORY_SEPARATOR . 'b',
                'one' . \DIRECTORY_SEPARATOR . 'b' . \DIRECTORY_SEPARATOR . 'c.neon',
                'one' . \DIRECTORY_SEPARATOR . 'b' . \DIRECTORY_SEPARATOR . 'd.neon',
            ],
        ];

        yield [
            '',
            'A',
            [
                'copy',
                'dolor.txt',
                'ipsum.txt',
                'lorem.txt',
                'one',
                'one' . \DIRECTORY_SEPARATOR . 'a',
                'one' . \DIRECTORY_SEPARATOR . 'b',
                'one' . \DIRECTORY_SEPARATOR . 'b' . \DIRECTORY_SEPARATOR . 'c.neon',
                'one' . \DIRECTORY_SEPARATOR . 'b' . \DIRECTORY_SEPARATOR . 'd.neon',
                'copy' . \DIRECTORY_SEPARATOR . 'A',
                'copy' . \DIRECTORY_SEPARATOR . 'A' . \DIRECTORY_SEPARATOR . 'a.dat.copy',
                'copy' . \DIRECTORY_SEPARATOR . 'A' . \DIRECTORY_SEPARATOR . 'B',
                'copy' . \DIRECTORY_SEPARATOR . 'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'ab.dat.copy',
                'copy' . \DIRECTORY_SEPARATOR . 'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C',
                'copy' . \DIRECTORY_SEPARATOR . 'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C' . \DIRECTORY_SEPARATOR . 'abc.dat.copy',
                'r+e.gex[c]a(r)s',
                'r+e.gex[c]a(r)s' . \DIRECTORY_SEPARATOR . 'dir',
                'r+e.gex[c]a(r)s' . \DIRECTORY_SEPARATOR . 'dir' . \DIRECTORY_SEPARATOR . 'bar.dat',
                'with space',
                'with space' . \DIRECTORY_SEPARATOR . 'foo.txt',
            ],
        ];
    }

    public function testAccessDeniedException(): void
    {
        $this->markAsSkippedIfChmodIsMissing();

        $this->finder->files()->in(self::$tmpDir);

        // make 'foo' directory non-readable
        $testDir = self::$tmpDir . \DIRECTORY_SEPARATOR . 'foo';

        \chmod($testDir, 0333);

        if (false === $couldRead = \is_readable($testDir)) {
            try {
                $this->assertIterator(self::toAbsolute(['foo bar', 'test.php', 'test.py']), $this->finder->getIterator());

                self::fail('Finder should throw an exception when opening a non-readable directory.');
            } catch (Exception $e) {
                if ($e instanceof ExpectationFailedException) {
                    self::fail(\sprintf("Expected exception:\n%s\nGot:\n%s\nWith comparison failure:\n%s", AccessDeniedException::class, ExpectationFailedException::class, $e->getComparisonFailure()->getExpectedAsString()));
                }

                self::assertInstanceOf(AccessDeniedException::class, $e);
            }
        }

        // restore original permissions
        \chmod($testDir, 0777);
        \clearstatcache(true, $testDir);

        if ($couldRead) {
            self::markTestSkipped('could read test files while test requires unreadable');
        }
    }

    public function testIgnoredAccessDeniedException(): void
    {
        $this->markAsSkippedIfChmodIsMissing();

        $this->finder->files()->ignoreUnreadableDirs()->in(self::$tmpDir);

        // make 'foo' directory non-readable
        $testDir = self::$tmpDir . \DIRECTORY_SEPARATOR . 'foo';

        \chmod($testDir, 0333);

        if (($couldRead = \is_readable($testDir)) === false) {
            $this->assertIterator(self::toAbsolute(
                [
                    'atime.php',
                    'foo bar',
                    'test.php',
                    'test.py',
                    'qux/baz_100_1.py',
                    'qux/baz_1_2.py',
                    'qux_0_1.php',
                    'qux_1000_1.php',
                    'qux_1002_0.php',
                    'qux_10_2.php',
                    'qux_12_0.php',
                    'qux_2_0.php',
                ]
            ), $this->finder->getIterator());
        }

        // restore original permissions
        \chmod($testDir, 0777);
        \clearstatcache(true, $testDir);

        if ($couldRead) {
            self::markTestSkipped('could read test files while test requires unreadable');
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
     * @return string
     */
    protected static function getTempPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'viserio_finder';
    }
}
