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
