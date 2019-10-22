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
use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Dumper\PhpDumper;
use Viserio\Component\Container\PhpParser\PrettyPrinter;

/**
 * @Groups({"viserio-closure", "container"}, extend=true)
 * @BeforeClassMethods({"clearCache", "warmup"})
 */
class ViserioClosureContainerBench extends ContainerBenchCase
{
    /** @var \Viserio\Contract\Container\CompiledContainer */
    private $container;

    public static function getContainer(): ContainerBuilder
    {
        $builder = new ContainerBuilder();

        $builder->singleton('factory_shared', static function () {
            return new EmptyFactory();
        })
            ->setExecutable(true)
            ->setPublic(true);

        $builder->bind('factory', static function () {
            return new EmptyFactory();
        })
            ->setExecutable(true)
            ->setPublic(true);

        return $builder;
    }

    public static function warmup(): void
    {
        $builder = self::getContainer();
        $builder->compile();

        $dumper = new PhpDumper(
            $builder,
            (new ParserFactory())->create(
                ParserFactory::PREFER_PHP7,
                new Emulative([
                    'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
                ])
            ),
            new PrettyPrinter()
        );
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
