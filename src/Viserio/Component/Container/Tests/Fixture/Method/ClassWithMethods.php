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

namespace Viserio\Component\Container\Tests\Fixture\Method;

class ClassWithMethods
{
    public function foo(): int
    {
        return 42;
    }

    public static function bar(): int
    {
        return 24;
    }

    public static function selfMethod(
        self $class,
        self $self,
        ?Undefined $nullable1 = null,
        ?int $nullable2 = null
    ): array {
        return [$class, $self, $nullable1, $nullable2];
    }

    public static function nullableMethod(?self $class, ?self $self, ?Undefined $nullable1, ?int $nullable2): array
    {
        return [$class, $self, $nullable1, $nullable2];
    }
}
