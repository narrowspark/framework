<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerResolverResolveMethodClass
{
    public static function selfMethod(self $class, self $self, Undefined $nullable1 = null, int $nullable2 = null)
    {
        return [$class, $self, $nullable1, $nullable2];
    }

    public static function methodNullable(?self $class, ?self $self, ?Undefined $nullable1, ?int $nullable2)
    {
        return [$class, $self, $nullable1, $nullable2];
    }
}
