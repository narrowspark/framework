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
