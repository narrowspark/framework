<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Narrowspark\Benchmark\Container;

use DI\Container;
use DI\ContainerBuilder;
use Narrowspark\Benchmark\Container\Fixture\EmptyFactory;

/**
 * @Groups({"php-di", "container"}, extend=true)
 */
class PhpDiContainerBench extends ContainerBenchCase
{
    private $container;

    public function initOptimized(): void
    {
        $builder = $this->createOptimizedBuilder();

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

    private function createOptimizedBuilder(): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        $builder->enableCompilation(self::getCacheDir());
        $builder->addDefinitions([
            'factory' => \DI\create(EmptyFactory::class),
        ]);

        return $builder;
    }
}
