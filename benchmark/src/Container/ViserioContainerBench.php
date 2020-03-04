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

use Narrowspark\Benchmark\Container\Fixture\EmptyFactory;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Dumper\PhpDumper;

/**
 * @Groups({"viserio", "container"}, extend=true)
 * @BeforeClassMethods({"clearCache", "warmup"})
 */
class ViserioContainerBench extends ContainerBenchCase
{
    /** @var \Viserio\Contract\Container\CompiledContainer */
    private $container;

    public static function getContainer(): ContainerBuilder
    {
        $builder = new ContainerBuilder();

        $builder->singleton('factory_shared', new EmptyFactory())
            ->setPublic(true);

        $builder->bind('factory', new EmptyFactory())
            ->setPublic(true);

        return $builder;
    }

    public static function warmup(): void
    {
        $builder = self::getContainer();
        $builder->compile();

        $dumper = new PhpDumper($builder);
        $className = 'ViserioContainerBench';

        \file_put_contents(
            self::getCacheDir() . \DIRECTORY_SEPARATOR . $className . '.php',
            $dumper->dump([
                'class' => $className,
            ])
        );
    }

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

    public function initOptimized(): void
    {
        require_once self::getCacheDir() . \DIRECTORY_SEPARATOR . 'ViserioContainerBench.php';

        $this->container = new \ViserioContainerBench();
    }

    public function initUnoptimized(): void
    {
        $this->container = self::getContainer();
    }
}
