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

namespace Viserio\Component\Container\Tests\Fixture\Proxy;

class ClassToProxy
{
    public static $destructorCount = 0;

    public $foo;

    public $moo;

    public $bar;

    public $initialized = false;

    public $configured = false;

    public $called = false;

    public $arguments = [];

    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function __destruct()
    {
        self::$destructorCount++;
    }

    public function setBar($value = null): void
    {
        $this->bar = $value;
    }

    public static function getInstance(array $arguments = []): self
    {
        $obj = new self($arguments);
        $obj->called = true;

        return $obj;
    }

    public function initialize(): void
    {
        $this->initialized = true;
    }

    public function configure(): void
    {
        $this->configured = true;
    }
}
