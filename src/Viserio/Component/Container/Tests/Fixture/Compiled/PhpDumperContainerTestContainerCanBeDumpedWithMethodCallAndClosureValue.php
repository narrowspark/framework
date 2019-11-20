<?php

declare(strict_types=1);

namespace Viserio\Component\Container\Tests\IntegrationTest\Dumper\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class PhpDumperContainerTestContainerCanBeDumpedWithMethodCallAndClosureValue extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->methodMapping = [
            'foo' => 'get55df4251026261c15e5362b72748729c5413605491a6b31caf07b0571c04af5f',
        ];
    }

    /**
     * Returns the public foo service.
     *
     * @return object
     */
    protected function get55df4251026261c15e5362b72748729c5413605491a6b31caf07b0571c04af5f(): object
    {
        $instance = new class()
{
    public function add(callable $container): void
    {
    }
};

        $instance->add(static function () : void {
});
        $instance->add(static function () {
    return 'test';
});

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedIds(): array
    {
        return [
            \Psr\Container\ContainerInterface::class => true,
            \Viserio\Contract\Container\Factory::class => true,
            \Viserio\Contract\Container\TaggedContainer::class => true,
            'container' => true,
        ];
    }
}
