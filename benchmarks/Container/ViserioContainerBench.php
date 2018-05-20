<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Container;

use Narrowspark\Benchmarks\Fixture\EmptyFactory;
use Viserio\Component\Container\Container;

/**
 * @Groups({"viserio", "container"}, extend=true)
 */
class ViserioContainerBench extends ContainerBenchCase
{
    /**
     * @var \Viserio\Component\Container\Container
     */
    private $container;

    /**
     * @BeforeMethods({"init"})
     */
    public function benchGetOptimized(): void
    {
        $this->container->get('factory_shared');
    }

    /**
     * @Skip
     */
    public function benchGetUnoptimized(): void
    {
    }

    public function benchGetPrototype(): void
    {
        $this->container->get('factory');
    }

    public function benchLifecycle(): void
    {
        $this->init();
        $this->container->get('factory_shared');
    }

    public function initOptimized(): void
    {
        $this->init();
    }

    public function initUnoptimized(): void
    {
        $this->init();
    }

    public function init(): void
    {
        $container = new Container();

        $container->singleton('factory_shared', function () {
            return new EmptyFactory();
        });

        $container->bind('factory', function () {
            return new EmptyFactory();
        });

        $this->container = $container;
    }
}
