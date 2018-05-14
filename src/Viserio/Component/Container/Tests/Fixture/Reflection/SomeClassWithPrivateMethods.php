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

namespace Viserio\Component\Container\Tests\Fixture\Reflection;

final class SomeClassWithPrivateMethods
{
    private function getNumber(): int
    {
        return 5;
    }

    private function plus10(int $number): int
    {
        return $number += 10;
    }

    private function multipleByTwo(int &$number): void
    {
        $number *= 2;
    }
}
