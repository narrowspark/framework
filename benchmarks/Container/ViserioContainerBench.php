<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Container;

use Narrowspark\Benchmarks\Fixture\EmptyFactory;
use Viserio\Component\Container\Container;
use Viserio\Component\Container\ContainerBuilder;

/**
 * @Groups({"viserio", "container"}, extend=true)
 */
class ViserioContainerBench extends ContainerBenchCase
{
    /**
     * @var \Viserio\Component\Container\Container
     */
    private $container;

    public function initUnoptimized(): void
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

    public function initOptimized(): void
    {
        $container = new ContainerBuilder();
        $container->bind('factory', function () {
            return new EmptyFactory();
        });
        $container->singleton('factory_shared', new EmptyFactory());

        $container->enableCompilation(self::getCacheDir());

        $this->container = $container->build();
    }

    public function benchGetOptimized(): void
    {
        $this->container->get('factory_shared');
    }

    public function benchGetUnoptimized(): void
    {
        $this->container->get('factory');
    }

    public function benchGetPrototype(): void
    {
        $this->container->get('factory');
    }
}
