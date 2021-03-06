<?php

declare(strict_types=1);

namespace Viserio\Component\Container\Tests\Integration\Dumper\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class PhpDumperContainerTestContainerCanBeDumpedWithClassNameInFactory extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->methodMapping = [
            \Viserio\Component\Container\Tests\Fixture\FactoryClass::class => 'gete6b75606779d33c92bdd90effd0a5fe3b4906a4344ab9854f91f135ba070b2f0',
            'foo' => 'get55df4251026261c15e5362b72748729c5413605491a6b31caf07b0571c04af5f',
        ];
    }

    /**
     * Returns the public Viserio\Component\Container\Tests\Fixture\FactoryClass shared service.
     *
     * @return \Viserio\Component\Container\Tests\Fixture\FactoryClass
     */
    protected function gete6b75606779d33c92bdd90effd0a5fe3b4906a4344ab9854f91f135ba070b2f0(): \Viserio\Component\Container\Tests\Fixture\FactoryClass
    {
        return $this->services[\Viserio\Component\Container\Tests\Fixture\FactoryClass::class] = new \Viserio\Component\Container\Tests\Fixture\FactoryClass();
    }

    /**
     * Returns the public foo service.
     *
     * @return mixed An instance returned by \Viserio\Component\Container\Definition\ReferenceDefinition::create()
     */
    protected function get55df4251026261c15e5362b72748729c5413605491a6b31caf07b0571c04af5f()
    {
        return ($this->services[\Viserio\Component\Container\Tests\Fixture\FactoryClass::class] ?? $this->gete6b75606779d33c92bdd90effd0a5fe3b4906a4344ab9854f91f135ba070b2f0())->create();
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
