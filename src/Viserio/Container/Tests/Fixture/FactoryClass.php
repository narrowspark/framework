<?php
namespace Viserio\Container\Tests\Fixture;

class FactoryClass
{
    public function create()
    {
        return 'Hello';
    }

    public function returnsParameters($param1, $param2)
    {
        return $param1 . $param2;
    }

    public static function staticCreate()
    {
        return 'Hello';
    }
}
