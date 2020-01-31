<?php

declare(strict_types=1);

namespace Viserio\Component\Container\Tests\Integration\Dumper\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class PhpDumperContainerTestArrayParameters extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->parameters = [
            'array_1' => [
                0 => 123,
            ],
            'array_2' => [
                0 => (\dirname(__DIR__, 2).'/IntegrationTest/Dumper'),
            ],
            'viserio.container.dumper.inline_class_loader' => false,
        ];
        $this->methodMapping = [
            'bar' => 'get91b123d3875702532e36683116824223d37b37377003156fc244abb2a82fec9c',
        ];
    }

    /**
     * Returns the public bar service.
     *
     * @return \Viserio\Component\Container\Tests\Fixture\FooClass
     */
    protected function get91b123d3875702532e36683116824223d37b37377003156fc244abb2a82fec9c(): \Viserio\Component\Container\Tests\Fixture\FooClass
    {
        $instance = new \Viserio\Component\Container\Tests\Fixture\FooClass();

        $instance->setBar($this->parameters['array_1'], $this->parameters['array_2'], '{array_1}', $this->parameters['array_1']);

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
