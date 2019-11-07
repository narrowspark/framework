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

use Narrowspark\Benchmark\Container\Fixture\EmptyFactory;
use ProjectServiceContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
 * @Groups({"symfony", "container"}, extend=true)
 * @BeforeClassMethods({"clearCache", "warmup"})
 */
class SymfonyDiContainerBench extends ContainerBenchCase
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public static function getContainer()
    {
        $builder = new ContainerBuilder();

        $protoDefinition = $builder->register('factory', EmptyFactory::class);
        $protoDefinition->setShared(false);
        $protoDefinition->setPublic(true);

        $definition = $builder->register('factory_shared', EmptyFactory::class);
        $definition->setPublic(true);

        return $builder;
    }

    public static function warmup(): void
    {
        $containerFile = self::getCacheDir() . \DIRECTORY_SEPARATOR . 'container.php';

        $builder = self::getContainer();
        $builder->compile();

        $dumper = new PhpDumper($builder);

        \file_put_contents($containerFile, $dumper->dump(['debug' => false, 'as_files' => false]));
    }

    public function benchGetOptimized(): void
    {
        $this->container->get('factory_shared');
    }

    public function benchGetUnoptimized(): void
    {
        $this->container->get('factory_shared');
    }

    public function benchGetPrototype(): void
    {
        $this->container->get('factory');
    }

    public function initOptimized(): void
    {
        require_once self::getCacheDir() . \DIRECTORY_SEPARATOR . 'container.php';

        $this->container = new ProjectServiceContainer();
    }

    public function initUnoptimized(): void
    {
        $this->container = self::getContainer();
    }
}
