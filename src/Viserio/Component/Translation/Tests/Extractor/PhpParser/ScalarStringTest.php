<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Extractor\PhpParser;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Extractor\PhpParser\ScalarString;

/**
 * The following is derived from code at http://github.com/nikic/PHP-Parser.
 *
 * Copyright (c) 2011-2018 by Nikita Popov.
 *
 * Some rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *
 *    * Redistributions in binary form must reproduce the above
 *      copyright notice, this list of conditions and the following
 *      disclaimer in the documentation and/or other materials provided
 *      with the distribution.
 *
 *    * The names of the contributors may not be used to endorse or
 *      promote products derived from this software without specific
 *      prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @internal
 */
final class ScalarStringTest extends TestCase
{
    /**
     * @dataProvider provideTestParseEscapeSequences
     *
     * @param mixed $expected
     * @param mixed $string
     * @param mixed $quote
     */
    public function testParseEscapeSequences($expected, $string, $quote): void
    {
        $this->assertSame(
            $expected,
            ScalarString::parseEscapeSequences($string, $quote)
        );
    }

    /**
     * @dataProvider provideTestParse
     *
     * @param mixed $expected
     * @param mixed $string
     */
    public function testCreate($expected, $string): void
    {
        $this->assertSame(
            $expected,
            ScalarString::parse($string)
        );
    }

    /**
     * @return array
     */
    public function provideTestParseEscapeSequences(): array
    {
        return [
            ['"',              '\\"',              '"'],
            ['\\"',            '\\"',              '`'],
            ['\\"\\`',         '\\"\\`',           null],
            ["\\\$\n\r\t\f\v", '\\\\\$\n\r\t\f\v', null],
            ["\x1B",           '\e',               null],
            [\chr(255),         '\xFF',             null],
            [\chr(255),         '\377',             null],
            [\chr(0),           '\400',             null],
            ["\0",             '\0',               null],
            ['\xFF',           '\\\\xFF',          null],
        ];
    }

    /**
     * @return array
     */
    public function provideTestParse(): array
    {
        $tests = [
            ['A', '\'A\''],
            ['A', 'b\'A\''],
            ['A', '"A"'],
            ['A', 'b"A"'],
            ['\\', '\'\\\\\''],
            ['\'', '\'\\\'\''],
        ];

        foreach ($this->provideTestParseEscapeSequences() as $i => $test) {
            // skip second and third tests, they aren't for double quotes
            if ($i !== 1 && $i !== 2) {
                $tests[] = [$test[0], '"' . $test[1] . '"'];
            }
        }

        return $tests;
    }
}
