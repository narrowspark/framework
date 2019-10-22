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

namespace Viserio\Component\View\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\View\Traits\NormalizeNameTrait;

/**
 * @internal
 *
 * @small
 */
final class NormalizeNameTraitTest extends TestCase
{
    use NormalizeNameTrait;

    /**
     * @dataProvider provideNormalizeNameCases
     *
     * @param mixed $name
     * @param mixed $validated
     */
    public function testNormalizeName($name, $validated): void
    {
        $validatedName = $this->normalizeName($name);

        self::assertSame($validated, $validatedName);
    }

    public function provideNormalizeNameCases(): iterable
    {
        return [
            ['test/foo', 'test.foo'],
            ['path::test/foo', 'path::test.foo'],
            ['deep/path::test/foo', 'deep/path::test.foo'],
        ];
    }
}
