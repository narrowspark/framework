<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture\Util;

class ReflectionGetParameterTypeClass
{
    public function method(Undeclared $undeclared, ReflectionToStringClass $b, array $array, callable $callable, $none, ?ReflectionToStringClass $nullable): void
    {
    }

    public function method2(Undeclared $undeclared, ReflectionToStringClass $b, array $array, callable $callable, self $self, $none): void
    {
    }
}
