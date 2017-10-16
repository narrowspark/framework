<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class FactoryClass
{
    public function create(): string
    {
        return 'Hello';
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
}
