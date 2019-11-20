<?php

declare(strict_types=1);

namespace Viserio\Component\Container\Tests\IntegrationTest\Dumper\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class PhpDumperContainerTestPreloadOptimizations extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->parameters = [
            'container.dumper.inline_class_loader' => true,
        ];
        $this->methodMapping = [
            \Viserio\Component\Container\Tests\Fixture\Preload\C1::class => 'get7c15f70d745f86fee064381a7f7884c4ba0e2123f94d95bbffb017c704505a54',
            \Viserio\Component\Container\Tests\Fixture\Preload\C2::class => 'geted275433f48a8253aaecdf41e0b086fe36dbcad602ae7cc45961cacf6fd9e0cd',
        ];

        include_once \dirname(__DIR__, 1).'/Preload/I1.php';
        include_once \dirname(__DIR__, 1).'/Preload/P1.php';
        include_once \dirname(__DIR__, 1).'/Preload/T1.php';
        include_once \dirname(__DIR__, 1).'/Preload/C1.php';
    }

    /**
     * Returns the public Viserio\Component\Container\Tests\Fixture\Preload\C1 shared service.
     *
     * @return \Viserio\Component\Container\Tests\Fixture\Preload\C1
     */
    protected function get7c15f70d745f86fee064381a7f7884c4ba0e2123f94d95bbffb017c704505a54(): \Viserio\Component\Container\Tests\Fixture\Preload\C1
    {
        return $this->services[\Viserio\Component\Container\Tests\Fixture\Preload\C1::class] = new \Viserio\Component\Container\Tests\Fixture\Preload\C1();
    }

    /**
     * Returns the public Viserio\Component\Container\Tests\Fixture\Preload\C2 shared service.
     *
     * @return \Viserio\Component\Container\Tests\Fixture\Preload\C2
     */
    protected function geted275433f48a8253aaecdf41e0b086fe36dbcad602ae7cc45961cacf6fd9e0cd(): \Viserio\Component\Container\Tests\Fixture\Preload\C2
    {
        include_once \dirname(__DIR__, 1).'/Preload/C2.php';
        include_once \dirname(__DIR__, 1).'/Preload/C3.php';

        return $this->services[\Viserio\Component\Container\Tests\Fixture\Preload\C2::class] = new \Viserio\Component\Container\Tests\Fixture\Preload\C2(new \Viserio\Component\Container\Tests\Fixture\Preload\C3());
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedIds(): array
    {
        return [
            \Psr\Container\ContainerInterface::class => true,
            \Viserio\Component\Container\Tests\Fixture\Preload\C3::class => true,
            \Viserio\Contract\Container\Factory::class => true,
            \Viserio\Contract\Container\TaggedContainer::class => true,
            'container' => true,
        ];
    }
}
