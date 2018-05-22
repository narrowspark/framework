<?php
declare(strict_types=1);
namespace Narrowspark\Benchmarks\Container;

use DI\Container;
use DI\ContainerBuilder;
use Narrowspark\Benchmarks\Fixture\EmptyFactory;

/**
 * @Groups({"php-di", "container"}, extend=true)
 */
class PhpDiContainerBench extends ContainerBenchCase
{
    private $container;

    public function initOptimized(): void
    {
        $builder   = $this->createOptimizedBuilder();

        $container = $builder->build();
        $container->get('factory');

        $this->container = $this->createOptimizedBuilder()->build();
    }

    public function initUnoptimized(): void
    {
        $this->container = new Container();
        $this->container->set('factory', \DI\create(EmptyFactory::class));
    }

    public function initPrototype(): void
    {
        $this->initOptimized();
    }

    public function benchGetOptimized(): void
    {
        $this->container->get('factory');
    }

    public function benchGetUnoptimized(): void
    {
        $this->container->get('factory');
    }

    public function benchGetPrototype(): void
    {
        $this->container->make('factory');
    }

    public function benchLifecycle(): void
    {
        $this->initOptimized();
        $this->container->get('factory');
    }

    private function createOptimizedBuilder()
    {
        $builder = new ContainerBuilder();
        $builder->enableCompilation(self::getCacheDir());
        $builder->addDefinitions([
            'factory' => \DI\create(EmptyFactory::class),
        ]);

        return $builder;
    }
}
