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

    /**
     * @return iterable
     */
    public function provideIsRegexCases(): iterable
    {
        yield ['foo', false, 'string'];

        yield [' foo ', false, '" " is not a valid delimiter'];

        yield ['\\foo\\', false, '"\\" is not a valid delimiter'];

        yield ['afooa', false, '"a" is not a valid delimiter'];

        yield ['//', false, 'the pattern should contain at least 1 character'];

        yield ['/a/', true, 'valid regex'];

        yield ['/foo/', true, 'valid regex'];

        yield ['/foo/i', true, 'valid regex with a single modifier'];

        yield ['/foo/imsxu', true, 'valid regex with multiple modifiers'];

        yield ['#foo#', true, '"#" is a valid delimiter'];

        yield ['{foo}', true, '"{,}" is a valid delimiter pair'];

        yield ['[foo]', true, '"[,]" is a valid delimiter pair'];

        yield ['(foo)', true, '"(,)" is a valid delimiter pair'];

        yield ['<foo>', true, '"<,>" is a valid delimiter pair'];

        yield ['*foo.*', false, '"*" is not considered as a valid delimiter'];

        yield ['?foo.?', false, '"?" is not considered as a valid delimiter'];
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
