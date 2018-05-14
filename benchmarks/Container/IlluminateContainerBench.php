<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Container;

use Illuminate\Container\Container;
use Narrowspark\Benchmarks\Fixture\EmptyFactory;

/**
 * @Groups({"illuminate", "container"}, extend=true)
 */
class IlluminateContainerBench extends ContainerBenchCase
{
    private $container;

    /**
     * @BeforeMethods({"init"})
     */
    public function benchGetOptimized(): void
    {
        $this->container['factory_shared'];
    }

    /**
     * @Skip
     */
    public function benchGetUnoptimized(): void
    {
    }

    public function benchGetPrototype(): void
    {
        $this->container['factory'];
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
        $container->singleton('factory_shared', function ($app) {
            return new EmptyFactory();
        });
        $container->bind('factory', function ($app) {
            return new EmptyFactory();
        }, false);

        $this->container = $container;
    }
}
