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

namespace Viserio\Component\View\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\View\Traits\NormalizeNameTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class NormalizeNameTraitTest extends TestCase
{
    use NormalizeNameTrait;

    /**
     * @dataProvider provideNormalizeNameCases
     */
    public function testNormalizeName($name, $validated): void
    {
        $validatedName = $this->normalizeName($name);

        self::assertSame($validated, $validatedName);
    }

    public static function provideNormalizeNameCases(): iterable
    {
        return [
            ['test/foo', 'test.foo'],
            ['path::test/foo', 'path::test.foo'],
            ['deep/path::test/foo', 'deep/path::test.foo'],
        ];
    }
}
