<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Finder\Tests\Filter;

use Viserio\Component\Finder\Filter\FilenameFilterIterator;
use Viserio\Component\Finder\Tests\AbstractIteratorTestCase;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class FilenameFilterIteratorTest extends AbstractIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     */
    public function testAccept($matchPatterns, $noMatchPatterns, $expected): void
    {
        $inner = new \Viserio\Component\Finder\Tests\Fixture\InnerNameIterator(['test.php', 'test.py', 'foo.php']);

        $iterator = new FilenameFilterIterator($inner, $matchPatterns, $noMatchPatterns);

        $this->assertIterator($expected, $iterator);
    }

    /**
     * @return iterable<array<array<string>>>
     */
    public static function provideAcceptCases(): iterable
    {
        yield [['test.*'], [], ['test.php', 'test.py']];

        yield [[], ['test.*'], ['foo.php']];

        yield [['*.php'], ['test.*'], ['foo.php']];

        yield [['*.php', '*.py'], ['foo.*'], ['test.php', 'test.py']];

        yield [['/\.php$/'], [], ['test.php', 'foo.php']];

        yield [[], ['/\.php$/'], ['test.py']];
    }
}
