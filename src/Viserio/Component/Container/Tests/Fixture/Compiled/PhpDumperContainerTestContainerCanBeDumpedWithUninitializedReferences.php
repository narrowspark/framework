<?php

declare(strict_types=1);

namespace Viserio\Component\Container\Tests\Integration\Dumper\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class PhpDumperContainerTestContainerCanBeDumpedWithUninitializedReferences extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->methodMapping = [
            'bar' => 'get91b123d3875702532e36683116824223d37b37377003156fc244abb2a82fec9c',
            'baz' => 'get601ebbf111eade1dea92676086466647f1ade8dccb6090fb680c0566ce14bea4',
            'foo1' => 'get3d08d944df91ca427a4bc4ea4de860e4ef2fce9cb5db080a4ff33bc9477aaa7c',
        ];
    }

    /**
     * Returns the public bar shared service.
     *
     * @return \stdClass
     */
    protected function get91b123d3875702532e36683116824223d37b37377003156fc244abb2a82fec9c(): \stdClass
    {
        $this->services['bar'] = $instance = new \stdClass();

        $instance->foo1 = ($this->services['foo1'] ?? null);
        $instance->foo2 = null;
        $instance->foo3 = ($this->privates['foo3'] ?? null);
        $instance->closures = [
            0 => function () {
            return ($this->services['foo1'] ?? null);
        },
            1 => static function () {
            return null;
        },
            2 => function () {
            return ($this->privates['foo3'] ?? null);
        },
        ];
        $instance->iter = new \Viserio\Component\Container\RewindableGenerator(function () {
            if (isset($this->services['foo1'])) {
                yield 'foo1' => ($this->services['foo1'] ?? null);
            }
            if (isset($this->privates['foo3'])) {
                yield 'foo3' => ($this->privates['foo3'] ?? null);
            }
        }, function () {
            return 0 + (int) (isset($this->services['foo1'])) + (int) (false) + (int) (isset($this->privates['foo3']));
        });

        return $instance;
    }

    /**
     * Returns the public baz shared service.
     *
     * @return \stdClass
     */
    protected function get601ebbf111eade1dea92676086466647f1ade8dccb6090fb680c0566ce14bea4(): \stdClass
    {
        $this->services['baz'] = $instance = new \stdClass();

        $instance->foo3 = ($this->privates['foo3'] ?? $this->getef2d83ee39b100fc3dc3e1fb6b8aadeb362fb915059fbf59639f12a41297f658());

        return $instance;
    }

    /**
     * Returns the public foo1 shared service.
     *
     * @return \stdClass
     */
    protected function get3d08d944df91ca427a4bc4ea4de860e4ef2fce9cb5db080a4ff33bc9477aaa7c(): \stdClass
    {
        return $this->services['foo1'] = new \stdClass();
    }

    /**
     * Returns the private foo3 shared service.
     *
     * @return \stdClass
     */
    protected function getef2d83ee39b100fc3dc3e1fb6b8aadeb362fb915059fbf59639f12a41297f658(): \stdClass
    {
        return $this->privates['foo3'] = new \stdClass();
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedIds(): array
    {
        return [
            \Psr\Container\ContainerInterface::class => true,
            \Viserio\Contract\Container\CompiledContainer::class => true,
            \Viserio\Contract\Container\Factory::class => true,
            \Viserio\Contract\Container\TaggedContainer::class => true,
            'container' => true,
            'foo2' => true,
            'foo3' => true,
        ];
    }
}
