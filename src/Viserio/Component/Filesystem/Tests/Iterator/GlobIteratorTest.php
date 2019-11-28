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

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Viserio\Component\Filesystem\Iterator\GlobIterator;
use Viserio\Component\Filesystem\Tests\TestStreamWrapper;
use function Viserio\Component\Filesystem\glob;

/**
 * @internal
 *
 * @small
 */
final class GlobIteratorTest extends AbstractBaseGlobFixtureTestCase
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

        TestStreamWrapper::register('globtest', $this->path);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        TestStreamWrapper::unregister('globtest');
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
        $iterator = new GlobIterator($this->path . '/**/*.css');

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
            $this->path . '/css/reset.css',
            $this->path . '/css/style.css',
        ], \iterator_to_array($iterator));
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
        $iterator = new GlobIterator($this->path . '/*css');

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
            $this->path . '/css',
        ], \iterator_to_array($iterator));
    }

    public function testDoubleWildcardMayMatchZeroCharacters(): void
    {
        $iterator = new GlobIterator($this->path . '/**/*css');

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
            $this->path . '/css',
            $this->path . '/css/reset.css',
            $this->path . '/css/style.css',
        ], \iterator_to_array($iterator));
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

        $this->assertSameAfterSorting([
            $this->path . '/base.css',
            $this->path . '/css',
            $this->path . '/css/reset.css',
            $this->path . '/css/style.css',
            $this->path . '/css/style.cts',
            $this->path . '/css/style.cxs',
            $this->path . '/js',
            $this->path . '/js/script.js',
        ], \iterator_to_array($iterator));
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

    public function testGlobStreamWrapper(): void
    {
        self::assertSame([
            'globtest:///base.css',
        ], glob('globtest:///*.css'));
        self::assertSame([
            'globtest:///base.css',
            'globtest:///css',
        ], glob('globtest:///*css*'));
        self::assertSame([
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ], glob('globtest:///*/*.css'));
        self::assertSame([
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
            'globtest:///css/style.cts',
            'globtest:///css/style.cxs',
        ], glob('globtest:///*/*.c?s'));
        self::assertSame([
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
            'globtest:///css/style.cts',
        ], glob('globtest:///*/*.c[st]s'));
        self::assertSame([
            'globtest:///css/style.cts',
        ], glob('globtest:///*/*.c[t]s'));
        self::assertSame([
            'globtest:///css/style.cts',
            'globtest:///css/style.cxs',
        ], glob('globtest:///*/*.c[t-x]s'));
        self::assertSame([
            'globtest:///css/style.cts',
            'globtest:///css/style.cxs',
        ], glob('globtest:///*/*.c[^s]s'));
        self::assertSame([
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ], glob('globtest:///*/*.c[^t-x]s'));
        self::assertSame([
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ], glob('globtest:///*/**/*.css'));
        self::assertSame([
            'globtest:///base.css',
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ], glob('globtest:///**/*.css'));
        self::assertSame([
            'globtest:///base.css',
            'globtest:///css',
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
        ], glob('globtest:///**/*css'));
        self::assertSame([
            'globtest:///base.css',
            'globtest:///css/reset.css',
        ], glob('globtest:///**/{base,reset}.css'));
        self::assertSame([
            'globtest:///css',
            'globtest:///css/reset.css',
            'globtest:///css/style.css',
            'globtest:///css/style.cts',
            'globtest:///css/style.cxs',
        ], glob('globtest:///css{,/**/*}'));
        self::assertSame([], glob('globtest:///*foo*'));
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

        try {
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
        } finally {
            foreach (glob($rootPath . '/css/style*.css') as $file) {
                @\unlink($file);
            }

            @\rmdir($rootPath . '/css');
            @\rmdir($rootPath);
        }
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
     * @dataProvider provideToRegExCases
     *
     * @param mixed $path
     * @param mixed $isMatch
     */
    public function testToRegEx($path, $isMatch): void
    {
        $regExp = GlobIterator::toRegEx('/foo/*.js~');

        self::assertSame($isMatch, \preg_match($regExp, $path));
    }

    /**
     * @dataProvider provideToRegExDoubleWildcardCases
     *
     * @param mixed $path
     * @param mixed $isMatch
     */
    public function testToRegExDoubleWildcard($path, $isMatch): void
    {
        $regExp = GlobIterator::toRegEx('/foo/**/*.js~');

        self::assertSame($isMatch, \preg_match($regExp, $path));
    }

    public function provideToRegExCases(): iterable
    {
        return [
            // The method assumes that the path is already consolidated
            ['/bar/baz.js~', 0],
            ['/foo/baz.js~', 1],
            ['/foo/../bar/baz.js~', 0],
            ['/foo/../foo/baz.js~', 0],
            ['/bar/baz.js', 0],
            ['/foo/bar/baz.js~', 0],
            ['foo/baz.js~', 0],
            ['/bar/foo/baz.js~', 0],
            ['/bar/.js~', 0],
        ];
    }

    public function provideToRegExDoubleWildcardCases(): iterable
    {
        return [
            ['/bar/baz.js~', 0],
            ['/foo/baz.js~', 1],
            ['/foo/../bar/baz.js~', 1],
            ['/foo/../foo/baz.js~', 1],
            ['/bar/baz.js', 0],
            ['/foo/bar/baz.js~', 1],
            ['foo/baz.js~', 0],
            ['/bar/foo/baz.js~', 0],
            ['/bar/.js~', 0],
        ];
    }

    // From the PHP manual: To specify a literal single quote, escape it with a
    // backslash (\). To specify a literal backslash, double it (\\).
    // All other instances of backslash will be treated as a literal backslash
    public function testEscapedWildcard(): void
    {
        // evaluates to "\*"
        $regExp = GlobIterator::toRegEx('/foo/\\*.js~');

        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/*.js~');
        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\\*.js~');
    }

    public function testEscapedWildcard2(): void
    {
        // evaluates to "\*"
        $regExp = GlobIterator::toRegEx('/foo/\*.js~');

        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/*.js~');
        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\\*.js~');
    }

    public function testMatchEscapedWildcard(): void
    {
        // evaluates to "\*"
        $regExp = GlobIterator::toRegEx('/foo/\\*.js~');

        self::assertRegExp($regExp, '/foo/*.js~');
    }

    public function testMatchEscapedDoubleWildcard(): void
    {
        // evaluates to "\*\*"
        $regExp = GlobIterator::toRegEx('/foo/\\*\\*.js~');

        self::assertRegExp($regExp, '/foo/**.js~');
    }

    public function testMatchWildcardWithLeadingBackslash(): void
    {
        // evaluates to "\\*"
        $regExp = GlobIterator::toRegEx('/foo/\\\\*.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchWildcardWithLeadingBackslash2(): void
    {
        // evaluates to "\\*"
        $regExp = GlobIterator::toRegEx('/foo/\\\*.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchEscapedWildcardWithLeadingBackslash(): void
    {
        // evaluates to "\\\*"
        $regExp = GlobIterator::toRegEx('/foo/\\\\\\*.js~');

        self::assertRegExp($regExp, '/foo/\\*.js~');
        self::assertRegExp($regExp, '/foo/\*.js~');
        self::assertNotRegExp($regExp, '/foo/*.js~');
        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\baz.js~');
    }

    public function testMatchWildcardWithTwoLeadingBackslashes(): void
    {
        // evaluates to "\\\\*"
        $regExp = GlobIterator::toRegEx('/foo/\\\\\\\\*.js~');

        self::assertRegExp($regExp, '/foo/\\\\baz.js~');
        self::assertRegExp($regExp, '/foo/\\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchEscapedWildcardWithTwoLeadingBackslashes(): void
    {
        // evaluates to "\\\\*"
        $regExp = GlobIterator::toRegEx('/foo/\\\\\\\\\\*.js~');

        self::assertRegExp($regExp, '/foo/\\\\*.js~');
        self::assertRegExp($regExp, '/foo/\\\*.js~');
        self::assertNotRegExp($regExp, '/foo/\\*.js~');
        self::assertNotRegExp($regExp, '/foo/\*.js~');
        self::assertNotRegExp($regExp, '/foo/*.js~');
        self::assertNotRegExp($regExp, '/foo/\\\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\\\baz.js~');
    }

    public function testMatchEscapedLeftBrace(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\{.js~');

        self::assertRegExp($regExp, '/foo/{.js~');
    }

    public function testMatchLeftBraceWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\\\{b,c}az.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchEscapedLeftBraceWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\\\\\{b,c}az.js~');

        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/\\{b,c}az.js~');
        self::assertRegExp($regExp, '/foo/\{b,c}az.js~');
        self::assertNotRegExp($regExp, '/foo/{b,c}az.js~');
    }

    public function testMatchUnescapedRightBraceWithoutLeadingLeftBrace(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/}.js~');

        self::assertRegExp($regExp, '/foo/}.js~');
    }

    public function testMatchEscapedRightBrace(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\}.js~');

        self::assertRegExp($regExp, '/foo/}.js~');
    }

    public function testMatchRightBraceWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/{b,c\\\\}az.js~');

        self::assertRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/c\\az.js~');
        self::assertRegExp($regExp, '/foo/c\az.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchEscapedRightBraceWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/{b,c\\\\\\}}az.js~');

        self::assertRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/c\\}az.js~');
        self::assertRegExp($regExp, '/foo/c\}az.js~');
        self::assertNotRegExp($regExp, '/foo/c\\az.js~');
        self::assertNotRegExp($regExp, '/foo/c\az.js~');
    }

    public function testCloseBracesAsSoonAsPossible(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/{b,c}}az.js~');

        self::assertRegExp($regExp, '/foo/b}az.js~');
        self::assertRegExp($regExp, '/foo/c}az.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchEscapedQuestionMark(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\?.js~');

        self::assertRegExp($regExp, '/foo/?.js~');
    }

    public function testMatchQuestionMarkWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\\\?az.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertRegExp($regExp, '/foo/\\caz.js~');
        self::assertRegExp($regExp, '/foo/\caz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchEscapedQuestionMarkWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\\\\\??az.js~');

        self::assertRegExp($regExp, '/foo/\\?baz.js~');
        self::assertRegExp($regExp, '/foo/\?baz.js~');
        self::assertRegExp($regExp, '/foo/\\?caz.js~');
        self::assertRegExp($regExp, '/foo/\?caz.js~');
        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\\caz.js~');
        self::assertNotRegExp($regExp, '/foo/\caz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchEscapedLeftBracket(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\[.js~');

        self::assertRegExp($regExp, '/foo/[.js~');
    }

    public function testMatchLeftBracketWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\\\[bc]az.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchEscapedLeftBracketWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\\\\\[bc]az.js~');

        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/\\[bc]az.js~');
        self::assertRegExp($regExp, '/foo/\[bc]az.js~');
        self::assertNotRegExp($regExp, '/foo/[bc]az.js~');
    }

    public function testMatchUnescapedRightBracketWithoutLeadingLeftBracket(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/].js~');

        self::assertRegExp($regExp, '/foo/].js~');
    }

    public function testMatchEscapedRightBracket(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\].js~');

        self::assertRegExp($regExp, '/foo/].js~');
    }

    public function testMatchRightBracketWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/[bc\\\\]az.js~');

        self::assertRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/caz.js~');
        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertNotRegExp($regExp, '/foo/bc\\az.js~');
        self::assertNotRegExp($regExp, '/foo/c\\az.js~');
        self::assertNotRegExp($regExp, '/foo/az.js~');
    }

    public function testMatchEscapedRightBracketWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/[bc\\\\\\]]az.js~');

        self::assertRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/caz.js~');
        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertRegExp($regExp, '/foo/]az.js~');
        self::assertNotRegExp($regExp, '/foo/bc\\]az.js~');
        self::assertNotRegExp($regExp, '/foo/c\\]az.js~');
        self::assertNotRegExp($regExp, '/foo/\\]az.js~');
    }

    public function testMatchUnescapedCaretWithoutLeadingLeftBracket(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/^.js~');

        self::assertRegExp($regExp, '/foo/^.js~');
    }

    public function testMatchEscapedCaret(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\^.js~');

        self::assertRegExp($regExp, '/foo/^.js~');
    }

    public function testMatchCaretWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/[\\\\^]az.js~');

        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertRegExp($regExp, '/foo/^az.js~');
        self::assertNotRegExp($regExp, '/foo/az.js~');
    }

    public function testMatchEscapedCaretWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/[\\\\\\^]az.js~');

        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertRegExp($regExp, '/foo/^az.js~');
        self::assertNotRegExp($regExp, '/foo/az.js~');
    }

    public function testMatchUnescapedHyphenWithoutLeadingLeftBracket(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/-.js~');

        self::assertRegExp($regExp, '/foo/-.js~');
    }

    public function testMatchEscapedHyphen(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/\\-.js~');

        self::assertRegExp($regExp, '/foo/-.js~');
    }

    public function testMatchHyphenWithLeadingBackslash(): void
    {
        // range from "\" to "a"
        $regExp = GlobIterator::toRegEx('/foo/[\\\\-a]az.js~');

        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertRegExp($regExp, '/foo/aaz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchEscapedHyphenWithLeadingBackslash(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/[\\\\\\-]az.js~');

        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertRegExp($regExp, '/foo/-az.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testCloseBracketsAsSoonAsPossible(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/[bc]]az.js~');

        self::assertRegExp($regExp, '/foo/b]az.js~');
        self::assertRegExp($regExp, '/foo/c]az.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchCharacterRanges(): void
    {
        $regExp = GlobIterator::toRegEx('/foo/[a-c]az.js~');

        self::assertRegExp($regExp, '/foo/aaz.js~');
        self::assertRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/caz.js~');
        self::assertNotRegExp($regExp, '/foo/daz.js~');
        self::assertNotRegExp($regExp, '/foo/eaz.js~');
    }

    public function testToRegexFailsIfNotAbsolute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('*.css');

        GlobIterator::toRegEx('*.css');
    }

    /**
     * @dataProvider provideGetStaticPrefixCases
     *
     * @param mixed $glob
     * @param mixed $prefix
     */
    public function testGetStaticPrefix($glob, $prefix): void
    {
        self::assertSame($prefix, GlobIterator::getStaticPrefix($glob));
    }

    public function provideGetStaticPrefixCases(): iterable
    {
        return [
            // The method assumes that the path is already consolidated
            ['/foo/baz/../*/bar/*', '/foo/baz/../'],
            ['/foo/baz/bar\\*', '/foo/baz/bar*'],
            ['/foo/baz/bar\\\\*', '/foo/baz/bar\\'],
            ['/foo/baz/bar\\\\\\*', '/foo/baz/bar\\*'],
            ['/foo/baz/bar\\\\\\\\*', '/foo/baz/bar\\\\'],
            ['/foo/baz/bar\\*\\\\', '/foo/baz/bar*\\'],
            ['/foo/baz/bar\\{a,b}', '/foo/baz/bar{a,b}'],
            ['/foo/baz/bar\\\\{a,b}', '/foo/baz/bar\\'],
            ['/foo/baz/bar\\\\\\{a,b}', '/foo/baz/bar\\{a,b}'],
            ['/foo/baz/bar\\\\\\\\{a,b}', '/foo/baz/bar\\\\'],
            ['/foo/baz/bar\\{a,b}\\\\', '/foo/baz/bar{a,b}\\'],
            ['/foo/baz/bar\\?', '/foo/baz/bar?'],
            ['/foo/baz/bar\\\\?', '/foo/baz/bar\\'],
            ['/foo/baz/bar\\\\\\?', '/foo/baz/bar\\?'],
            ['/foo/baz/bar\\\\\\\\?', '/foo/baz/bar\\\\'],
            ['/foo/baz/bar\\?\\\\', '/foo/baz/bar?\\'],
            ['/foo/baz/bar\\[ab]', '/foo/baz/bar[ab]'],
            ['/foo/baz/bar\\\\[ab]', '/foo/baz/bar\\'],
            ['/foo/baz/bar\\\\\\[ab]', '/foo/baz/bar\\[ab]'],
            ['/foo/baz/bar\\\\\\\\[ab]', '/foo/baz/bar\\\\'],
            ['/foo/baz/bar\\[ab]\\\\', '/foo/baz/bar[ab]\\'],
        ];
    }

    public function testGetStaticPrefixFailsIfNotAbsolute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('*.css');

        GlobIterator::getStaticPrefix('*.css');
    }

    /**
     * @dataProvider provideBasePaths
     *
     * @param mixed $glob
     * @param mixed $basePath
     * @param mixed $flags
     */
    public function testGetBasePath($glob, $basePath, $flags = 0): void
    {
        self::assertSame($basePath, GlobIterator::getBasePath($glob, $flags));
    }

    /**
     * @dataProvider provideBasePaths
     *
     * @param mixed $glob
     * @param mixed $basePath
     */
    public function testGetBasePathStream($glob, $basePath): void
    {
        self::assertSame('globtest://' . $basePath, GlobIterator::getBasePath('globtest://' . $glob));
    }

    public function provideBasePaths(): iterable
    {
        return [
            // The method assumes that the path is already consolidated
            ['/foo/baz/../*/bar/*', '/foo/baz/..'],
            ['/foo/baz/bar*', '/foo/baz'],
            ['/foo/baz/bar', '/foo/baz'],
            ['/foo/baz*', '/foo'],
            ['/foo*', '/'],
            ['/*', '/'],
            ['/foo/baz*/bar', '/foo'],
            ['/foo/baz\\*/bar', '/foo/baz*'],
            ['/foo/baz\\\\*/bar', '/foo'],
            ['/foo/baz\\\\\\*/bar', '/foo/baz\\*'],
        ];
    }

    public function testGetBasePathFailsIfNotAbsolute(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('*.css');

        GlobIterator::getBasePath('*.css');
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
