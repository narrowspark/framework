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
