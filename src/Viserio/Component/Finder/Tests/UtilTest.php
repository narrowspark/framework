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

use PHPUnit\Framework\TestCase;
use Viserio\Component\Finder\Util;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * @covers \Viserio\Component\Finder\Util
 *
 * @internal
 *
 * @small
 */
final class UtilTest extends TestCase
{
    /**
     * @dataProvider provideToRegExCases
     *
     * @param mixed $path
     * @param mixed $isMatch
     */
    public function testToRegEx($path, $isMatch): void
    {
        $regExp = Util::toRegEx('/foo/*.js~');

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
        $regExp = Util::toRegEx('/foo/**/*.js~');

        self::assertSame($isMatch, \preg_match($regExp, $path));
    }

    /**
     * @return iterable<array<int, int|string>>
     */
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

    /**
     * @return iterable<array<int, int|string>>
     */
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
        $regExp = Util::toRegEx('/foo/\\*.js~');

        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/*.js~');
        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\\*.js~');
    }

    public function testEscapedWildcard2(): void
    {
        // evaluates to "\*"
        $regExp = Util::toRegEx('/foo/\*.js~');

        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/*.js~');
        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\\*.js~');
    }

    public function testMatchEscapedWildcard(): void
    {
        // evaluates to "\*"
        $regExp = Util::toRegEx('/foo/\\*.js~');

        self::assertRegExp($regExp, '/foo/*.js~');
    }

    public function testMatchEscapedDoubleWildcard(): void
    {
        // evaluates to "\*\*"
        $regExp = Util::toRegEx('/foo/\\*\\*.js~');

        self::assertRegExp($regExp, '/foo/**.js~');
    }

    public function testMatchWildcardWithLeadingBackslash(): void
    {
        // evaluates to "\\*"
        $regExp = Util::toRegEx('/foo/\\\\*.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchWildcardWithLeadingBackslash2(): void
    {
        // evaluates to "\\*"
        $regExp = Util::toRegEx('/foo/\\\*.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchEscapedWildcardWithLeadingBackslash(): void
    {
        // evaluates to "\\\*"
        $regExp = Util::toRegEx('/foo/\\\\\\*.js~');

        self::assertRegExp($regExp, '/foo/\\*.js~');
        self::assertRegExp($regExp, '/foo/\*.js~');
        self::assertNotRegExp($regExp, '/foo/*.js~');
        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\baz.js~');
    }

    public function testMatchWildcardWithTwoLeadingBackslashes(): void
    {
        // evaluates to "\\\\*"
        $regExp = Util::toRegEx('/foo/\\\\\\\\*.js~');

        self::assertRegExp($regExp, '/foo/\\\\baz.js~');
        self::assertRegExp($regExp, '/foo/\\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchEscapedWildcardWithTwoLeadingBackslashes(): void
    {
        // evaluates to "\\\\*"
        $regExp = Util::toRegEx('/foo/\\\\\\\\\\*.js~');

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
        $regExp = Util::toRegEx('/foo/\\{.js~');

        self::assertRegExp($regExp, '/foo/{.js~');
    }

    public function testMatchLeftBraceWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/\\\\{b,c}az.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchEscapedLeftBraceWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/\\\\\\{b,c}az.js~');

        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/\\{b,c}az.js~');
        self::assertRegExp($regExp, '/foo/\{b,c}az.js~');
        self::assertNotRegExp($regExp, '/foo/{b,c}az.js~');
    }

    public function testMatchUnescapedRightBraceWithoutLeadingLeftBrace(): void
    {
        $regExp = Util::toRegEx('/foo/}.js~');

        self::assertRegExp($regExp, '/foo/}.js~');
    }

    public function testMatchEscapedRightBrace(): void
    {
        $regExp = Util::toRegEx('/foo/\\}.js~');

        self::assertRegExp($regExp, '/foo/}.js~');
    }

    public function testMatchRightBraceWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/{b,c\\\\}az.js~');

        self::assertRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/c\\az.js~');
        self::assertRegExp($regExp, '/foo/c\az.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchEscapedRightBraceWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/{b,c\\\\\\}}az.js~');

        self::assertRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/c\\}az.js~');
        self::assertRegExp($regExp, '/foo/c\}az.js~');
        self::assertNotRegExp($regExp, '/foo/c\\az.js~');
        self::assertNotRegExp($regExp, '/foo/c\az.js~');
    }

    public function testCloseBracesAsSoonAsPossible(): void
    {
        $regExp = Util::toRegEx('/foo/{b,c}}az.js~');

        self::assertRegExp($regExp, '/foo/b}az.js~');
        self::assertRegExp($regExp, '/foo/c}az.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchEscapedQuestionMark(): void
    {
        $regExp = Util::toRegEx('/foo/\\?.js~');

        self::assertRegExp($regExp, '/foo/?.js~');
    }

    public function testMatchQuestionMarkWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/\\\\?az.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertRegExp($regExp, '/foo/\\caz.js~');
        self::assertRegExp($regExp, '/foo/\caz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchEscapedQuestionMarkWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/\\\\\\??az.js~');

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
        $regExp = Util::toRegEx('/foo/\\[.js~');

        self::assertRegExp($regExp, '/foo/[.js~');
    }

    public function testMatchLeftBracketWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/\\\\[bc]az.js~');

        self::assertRegExp($regExp, '/foo/\\baz.js~');
        self::assertRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testMatchEscapedLeftBracketWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/\\\\\\[bc]az.js~');

        self::assertNotRegExp($regExp, '/foo/\\baz.js~');
        self::assertNotRegExp($regExp, '/foo/\baz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/\\[bc]az.js~');
        self::assertRegExp($regExp, '/foo/\[bc]az.js~');
        self::assertNotRegExp($regExp, '/foo/[bc]az.js~');
    }

    public function testMatchUnescapedRightBracketWithoutLeadingLeftBracket(): void
    {
        $regExp = Util::toRegEx('/foo/].js~');

        self::assertRegExp($regExp, '/foo/].js~');
    }

    public function testMatchEscapedRightBracket(): void
    {
        $regExp = Util::toRegEx('/foo/\\].js~');

        self::assertRegExp($regExp, '/foo/].js~');
    }

    public function testMatchRightBracketWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/[bc\\\\]az.js~');

        self::assertRegExp($regExp, '/foo/baz.js~');
        self::assertRegExp($regExp, '/foo/caz.js~');
        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertNotRegExp($regExp, '/foo/bc\\az.js~');
        self::assertNotRegExp($regExp, '/foo/c\\az.js~');
        self::assertNotRegExp($regExp, '/foo/az.js~');
    }

    public function testMatchEscapedRightBracketWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/[bc\\\\\\]]az.js~');

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
        $regExp = Util::toRegEx('/foo/^.js~');

        self::assertRegExp($regExp, '/foo/^.js~');
    }

    public function testMatchEscapedCaret(): void
    {
        $regExp = Util::toRegEx('/foo/\\^.js~');

        self::assertRegExp($regExp, '/foo/^.js~');
    }

    public function testMatchCaretWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/[\\\\^]az.js~');

        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertRegExp($regExp, '/foo/^az.js~');
        self::assertNotRegExp($regExp, '/foo/az.js~');
    }

    public function testMatchEscapedCaretWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/[\\\\\\^]az.js~');

        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertRegExp($regExp, '/foo/^az.js~');
        self::assertNotRegExp($regExp, '/foo/az.js~');
    }

    public function testMatchUnescapedHyphenWithoutLeadingLeftBracket(): void
    {
        $regExp = Util::toRegEx('/foo/-.js~');

        self::assertRegExp($regExp, '/foo/-.js~');
    }

    public function testMatchEscapedHyphen(): void
    {
        $regExp = Util::toRegEx('/foo/\\-.js~');

        self::assertRegExp($regExp, '/foo/-.js~');
    }

    public function testMatchHyphenWithLeadingBackslash(): void
    {
        // range from "\" to "a"
        $regExp = Util::toRegEx('/foo/[\\\\-a]az.js~');

        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertRegExp($regExp, '/foo/aaz.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchEscapedHyphenWithLeadingBackslash(): void
    {
        $regExp = Util::toRegEx('/foo/[\\\\\\-]az.js~');

        self::assertRegExp($regExp, '/foo/\\az.js~');
        self::assertRegExp($regExp, '/foo/-az.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
    }

    public function testCloseBracketsAsSoonAsPossible(): void
    {
        $regExp = Util::toRegEx('/foo/[bc]]az.js~');

        self::assertRegExp($regExp, '/foo/b]az.js~');
        self::assertRegExp($regExp, '/foo/c]az.js~');
        self::assertNotRegExp($regExp, '/foo/baz.js~');
        self::assertNotRegExp($regExp, '/foo/caz.js~');
    }

    public function testMatchCharacterRanges(): void
    {
        $regExp = Util::toRegEx('/foo/[a-c]az.js~');

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

        Util::toRegEx('*.css');
    }

    /**
     * @dataProvider provideGetStaticPrefixCases
     *
     * @param mixed $glob
     * @param mixed $prefix
     */
    public function testGetStaticPrefix($glob, $prefix): void
    {
        self::assertSame($prefix, Util::getStaticPrefix($glob));
    }

    /**
     * @return iterable<array<int, string>>
     */
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

        Util::getStaticPrefix('*.css');
    }

    /**
     * @dataProvider provideBasePaths
     *
     * @param mixed $glob
     * @param mixed $basePath
     */
    public function testGetBasePath($glob, $basePath): void
    {
        self::assertSame($basePath, Util::getBasePath($glob));
    }

    /**
     * @dataProvider provideBasePaths
     *
     * @param mixed $glob
     * @param mixed $basePath
     */
    public function testGetBasePathStream($glob, $basePath): void
    {
        self::assertSame('globtest://' . $basePath, Util::getBasePath('globtest://' . $glob));
    }

    /**
     * @return iterable<array<int, string>>
     */
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

        Util::getBasePath('*.css');
    }

    public function testNonMatchingGlobReturnsArray(): void
    {
        $result = Util::polyfillGlobBrace('/some/path/{,*.}{this,orthis}.php');

        self::assertCount(0, $result);
    }

    /**
     * @return iterable
     */
    public function providePatternsCases(): iterable
    {
        yield [
            '{{,*.}alph,{,*.}bet}a',
            [
                'alpha',
                'eta.alpha',
                'zeta.alpha',
                'beta',
                'eta.beta',
                'zeta.beta',
            ],
        ];

        yield [
            '/*{/*/*.txt,.x{m,n}l}',
            [
                'AnotherExcludedFile.txt',
                'foo.xml',
            ],
        ];

        // UnbalancedBraceFallback
        yield [
            '/*{/*/*.txt,.x{m,nl}',
            [],
        ];
    }

    /**
     * @dataProvider providePatternsCases
     *
     * @param string        $pattern
     * @param array<string> $expectedSequence
     */
    public function testPatterns(string $pattern, array $expectedSequence): void
    {
        $result = Util::polyfillGlobBrace(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Glob' . \DIRECTORY_SEPARATOR . $pattern);

        self::assertCount(\count($expectedSequence), $result);

        foreach ($expectedSequence as $i => $expectedFileName) {
            self::assertStringEndsWith($expectedFileName, $result[$i]);
        }
    }
}
