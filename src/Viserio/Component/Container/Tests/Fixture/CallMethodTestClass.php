<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class CallMethodTestClass
{
    public function foo(): int
    {
        return 42;
    }

    public static function bar(): int
    {
        return 24;
    }
}
