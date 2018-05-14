<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Narrowspark\Benchmark\Container;

use Illuminate\Container\Container;
use Narrowspark\Benchmark\Container\Fixture\EmptyFactory;

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
