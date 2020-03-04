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

use SplFileInfo;
use Viserio\Component\Finder\Filter\CustomFilterIterator;
use Viserio\Component\Finder\Tests\AbstractIteratorTestCase;
use Viserio\Component\Finder\Tests\Fixture\Iterator;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class CustomFilterIteratorTest extends AbstractIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     */
    public function testAccept($filters, $expected): void
    {
        $inner = new Iterator(['test.php', 'test.py', 'foo.php']);

        $iterator = new CustomFilterIterator($inner, ...$filters);

        $this->assertIterator($expected, $iterator);
    }

    /**
     * @return iterable<array<int, array<int, (Closure(SplFileInfo): bool)|string>>>
     */
    public static function provideAcceptCases(): iterable
    {
        yield [[static function (SplFileInfo $fileinfo): bool {
            return false;
        }], []];

        yield [[static function (SplFileInfo $fileinfo): bool {
            return 0 === \strpos($fileinfo->getPathname(), 'test');
        }], ['test.php', 'test.py']];

        yield [[static function (SplFileInfo $fileInfo): bool {
            return \is_dir($fileInfo->getPathname());
        }], []];
    }
}
