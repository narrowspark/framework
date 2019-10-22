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

class CallFixture
{
    public function __call($method, $arguments)
    {
        return \call_user_func_array([new TestClass($method, __CLASS__), $method], $arguments);
    }

    public static function __callStatic($method, $arguments)
    {
        return \call_user_func_array([new TestClass($method, __CLASS__), $method], $arguments);
    }
}

class TestClass
{
    public $setterParam1;

    public $setterParam2;

    public $constructorParam1;

    public $constructorParam2;

    public $publicField;

    public function __construct($param1, $param2)
    {
        $this->constructorParam1 = $param1;
        $this->constructorParam2 = $param2;
    }

    public function setSomething($param1, $param2): self
    {
        $this->setterParam1 = $param1;
        $this->setterParam2 = $param2;

        return $this;
    }
}
