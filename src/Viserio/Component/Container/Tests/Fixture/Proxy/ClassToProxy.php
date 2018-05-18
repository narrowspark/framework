<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture\Proxy;

class ClassToProxy
{
    public function foo(): void
    {
    }

    public function getInstance()
    {
        return $this;
    }
}
