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

use ArrayIterator;
use SplFileInfo;
use Viserio\Component\Finder\Filter\FilenameFilterIterator;
use Viserio\Component\Finder\Tests\IteratorTestCase;

/**
 * @internal
 *
 * @small
 */
final class FilenameFilterIteratorTest extends IteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     *
     * @param mixed $matchPatterns
     * @param mixed $noMatchPatterns
     * @param mixed $expected
     */
    public function testAccept($matchPatterns, $noMatchPatterns, $expected): void
    {
        $inner = new InnerNameIterator(['test.php', 'test.py', 'foo.php']);

        $iterator = new FilenameFilterIterator($inner, $matchPatterns, $noMatchPatterns);

        $this->assertIterator($expected, $iterator);
    }

    /**
     * @return iterable
     */
    public function provideAcceptCases(): iterable
    {
        yield [['test.*'], [], ['test.php', 'test.py']];

        yield [[], ['test.*'], ['foo.php']];

        yield [['*.php'], ['test.*'], ['foo.php']];

        yield [['*.php', '*.py'], ['foo.*'], ['test.php', 'test.py']];

        yield [['/\.php$/'], [], ['test.php', 'foo.php']];

        yield [[], ['/\.php$/'], ['test.py']];
    }
}

class InnerNameIterator extends ArrayIterator
{
    public function current()
    {
        return new SplFileInfo(parent::current());
    }

    public function getFilename()
    {
        return parent::current();
    }
}
