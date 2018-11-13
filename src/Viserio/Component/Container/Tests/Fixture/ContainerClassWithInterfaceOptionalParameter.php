<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

interface TestInterface
{
}

class ContainerClassWithInterfaceOptionalParameter
{
    /**
     * The parameter is optional. TestInterface is not instantiable, so `null` should
     * be injected instead of getting an exception.
     *
     * @param null|\Viserio\Component\Container\Tests\Fixture\TestInterface $param
     */
    public function __construct(TestInterface $param = null)
    {
    }
}
