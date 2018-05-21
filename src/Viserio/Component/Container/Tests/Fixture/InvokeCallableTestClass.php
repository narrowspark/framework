<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class InvokeCallableTestClass
{
    public function __invoke()
    {
        return 42;
    }
}
