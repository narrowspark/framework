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

namespace Viserio\Component\View\Container\Pipeline;

use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\View\Engine\IteratorViewEngineLoader;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Pipe as PipeContract;
use Viserio\Contract\View\Engine as EngineContract;
use Viserio\Contract\View\EngineResolver as EngineResolverContract;

class AddViewEnginePipe implements PipeContract
{
    /** @var string */
    private $commandTag;

    /**
     * Create a new AddViewEnginePipe instance.
     */
    public function __construct(string $commandTag = 'view.engine')
    {
        $this->commandTag = $commandTag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $lazyViewEngines = [];

        foreach ($containerBuilder->getTagged($this->commandTag) as $definitionAndTags) {
            [$definition] = $definitionAndTags;

            if (! $definition instanceof ObjectDefinitionContract) {
                continue;
            }

            $id = $definition->getName();
            $class = $definition->getClass();

            if (! $r = $containerBuilder->getClassReflector($class)) {
                throw new InvalidArgumentException(\sprintf('Class [%s] used for service [%s] cannot be found.', $class, $id));
            }

            if (! $r->implementsInterface(EngineContract::class)) {
                throw new InvalidArgumentException(\sprintf('The service [%s] tagged [%s] must have [%s] as interface.', $id, $this->commandTag, EngineContract::class));
            }

            /* @var \Viserio\Contract\View\Engine $class */
            foreach ($class::getDefaultNames() as $name) {
                $lazyViewEngines[$name] = (new ReferenceDefinition($id))->setType($class);
            }
        }

        $containerBuilder->bind(EngineResolverContract::class, IteratorViewEngineLoader::class)
            ->addArgument(new IteratorArgument($lazyViewEngines))
            ->setPublic(false);
        $containerBuilder->setAlias(EngineResolverContract::class, 'view.engine.resolver');
    }
}
