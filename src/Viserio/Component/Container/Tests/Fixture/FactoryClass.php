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

namespace Viserio\Component\Container\Tests\Fixture;

use Viserio\Component\Container\Tests\Fixture\Autowire\CollisionInterface;

class FactoryClass
{
    public function create(): string
    {
        return 'Hello';
    }

    public function add(CollisionInterface $a): void
    {
    }

    public function returnsParameters($param1, $param2): string
    {
        return $param1 . $param2;
    }

    public static function staticCreate(): string
    {
        return 'Hello';
    }

    public static function staticCreateWitArg($name)
    {
        return $name;
    }

    public function createFooClass(): FooClass
    {
        return new FooClass();
    }

    public function getInstance(): self
    {
        return $this;
    }
}
