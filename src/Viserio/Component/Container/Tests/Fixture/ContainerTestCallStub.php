<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerTestCallStub
{
    public function work()
    {
        return \func_get_args();
    }

    public function inject(ContainerContractFixtureInterface $stub, $default = 'foo')
    {
        return \func_get_args();
    }

    public function unresolvable($foo, $bar)
    {
        return \func_get_args();
    }
}
