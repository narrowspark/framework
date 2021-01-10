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

class FooClass
{
    public $foo;

    public $moo;

    public $bar;

    public $initialized = false;

    public $called = false;

    public $arguments = [];

    public function __construct($arguments = [])
    {
        $this->arguments = $arguments;
    }

    public function setBar($value = null): void
    {
        $this->bar = $value;
    }

    public static function getInstance($arguments = [])
    {
        $obj = new self($arguments);
        $obj->called = true;

        return $obj;
    }

    public function initialize(): void
    {
        $this->initialized = true;
    }
}
