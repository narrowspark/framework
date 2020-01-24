<?php

declare(strict_types=1);

namespace Viserio\Component\Container\Tests\IntegrationTest\Dumper\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class PhpDumperContainerTestUninitializedSyntheticReference extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->parameters = [
            'viserio.container.dumper.inline_class_loader' => true,
        ];
        $this->methodMapping = [
            'bar' => 'get91b123d3875702532e36683116824223d37b37377003156fc244abb2a82fec9c',
        ];
        $this->uninitializedServices = [
            'foo' => true,
        ];
        $this->syntheticIds = [
            'foo' => true,
        ];
    }

    /**
     * Returns the public bar service.
     *
     * @return \stdClass
     */
    protected function get91b123d3875702532e36683116824223d37b37377003156fc244abb2a82fec9c(): \stdClass
    {
        $instance = new \stdClass();

        $instance->foo = ($this->services['foo'] ?? $this->get('foo'));

        return $instance;
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
        ];
    }
}
