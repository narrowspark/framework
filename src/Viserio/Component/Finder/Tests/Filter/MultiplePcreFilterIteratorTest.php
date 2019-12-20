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

namespace Viserio\Component\Finder\Tests\Filter;

use BadFunctionCallException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Finder\Filter\AbstractMultiplePcreFilterIterator;

/**
 * @internal
 *
 * @small
 */
final class MultiplePcreFilterIteratorTest extends TestCase
{
    /**
     * @dataProvider provideIsRegexCases
     *
     * @param mixed $string
     * @param mixed $isRegex
     * @param mixed $message
     */
    public function testIsRegex($string, $isRegex, $message): void
    {
        $testIterator = new TestMultiplePcreFilterIterator();
        self::assertEquals($isRegex, $testIterator->isRegex($string), $message);
    }

    public function provideIsRegexCases(): iterable
    {
        return [
            ['foo', false, 'string'],
            [' foo ', false, '" " is not a valid delimiter'],
            ['\\foo\\', false, '"\\" is not a valid delimiter'],
            ['afooa', false, '"a" is not a valid delimiter'],
            ['//', false, 'the pattern should contain at least 1 character'],
            ['/a/', true, 'valid regex'],
            ['/foo/', true, 'valid regex'],
            ['/foo/i', true, 'valid regex with a single modifier'],
            ['/foo/imsxu', true, 'valid regex with multiple modifiers'],
            ['#foo#', true, '"#" is a valid delimiter'],
            ['{foo}', true, '"{,}" is a valid delimiter pair'],
            ['[foo]', true, '"[,]" is a valid delimiter pair'],
            ['(foo)', true, '"(,)" is a valid delimiter pair'],
            ['<foo>', true, '"<,>" is a valid delimiter pair'],
            ['*foo.*', false, '"*" is not considered as a valid delimiter'],
            ['?foo.?', false, '"?" is not considered as a valid delimiter'],
        ];
    }
}

class TestMultiplePcreFilterIterator extends AbstractMultiplePcreFilterIterator
{
    public function __construct()
    {
    }

    public function accept(): void
    {
        throw new BadFunctionCallException('Not implemented.');
    }

    public function isRegex(string $str): bool
    {
        return parent::isRegex($str);
    }

    public function toRegex(string $str): string
    {
        throw new BadFunctionCallException('Not implemented.');
    }
}
