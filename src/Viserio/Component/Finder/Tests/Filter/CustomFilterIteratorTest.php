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

use SplFileInfo;
use Viserio\Component\Finder\Filter\CustomFilterIterator;
use Viserio\Component\Finder\Tests\AbstractIteratorTestCase;
use Viserio\Component\Finder\Tests\Fixture\Iterator;

/**
 * @internal
 *
 * @small
 */
final class CustomFilterIteratorTest extends AbstractIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     *
     * @param mixed $filters
     * @param mixed $expected
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
