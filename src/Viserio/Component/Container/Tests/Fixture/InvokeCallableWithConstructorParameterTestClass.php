<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class InvokeCallableWithConstructorParameterTestClass
{
    private $class;

    public function __construct(FactoryClass $class)
    {
        $this->class = $class;
    }

    public function __invoke()
    {
        return $this->class;
    }
}
