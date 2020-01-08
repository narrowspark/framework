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

namespace Viserio\Component\Config\Container\Pipeline;

use ArrayIterator;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Pipe as PipeContract;
use Viserio\Contract\Container\Processor\DynamicParameterProcessor as DynamicParameterProcessorContract;
use Viserio\Contract\Container\Processor\ParameterProcessor as ParameterProcessorContract;

class RegisterParameterProcessorsPipe implements PipeContract
{
    /** @var string */
    public const TAG = 'container.parameter.processor';

    /** @var string */
    private string $tag;

    /**
     * Create a new RegisterParameterProcessorsPipe instance.
     *
     * @param string $tag
     */
    public function __construct(string $tag = self::TAG)
    {
        $this->tag = $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $processorRefs = [];
        $dynamicProcessorRefs = [];

        foreach ($containerBuilder->getTagged($this->tag) as $definitionAndTags) {
            [$definition] = $definitionAndTags;

            if (! $definition instanceof ObjectDefinitionContract) {
                continue;
            }

            $id = $definition->getName();
            $class = $definition->getClass();

            if (! $r = $containerBuilder->getClassReflector($class)) {
                throw new InvalidArgumentException(\sprintf('Class [%s] used for service [%s] cannot be found.', $class, $id));
            }

            if (! $r->implementsInterface(ParameterProcessorContract::class)) {
                throw new InvalidArgumentException(\sprintf('The service [%s] tagged [%s] must implement interface [%s].', $id, $this->tag, ParameterProcessorContract::class));
            }

            $refDefinition = (new ReferenceDefinition($id))
                ->setType($class);

            if ($r->implementsInterface(DynamicParameterProcessorContract::class)) {
                $dynamicProcessorRefs[] = $refDefinition;
            } else {
                foreach ($definition->getArguments() as $argument) {
                    if ($argument instanceof DefinitionContract) {
                        throw new InvalidArgumentException('@todo write exception.');
                    }
                }

                $processorRefs[] = $refDefinition;
            }
        }

        $containerBuilder->singleton('container.parameter.dynamic.processors', ArrayIterator::class)
            ->addArgument($dynamicProcessorRefs)
            ->setPublic(true);
        $containerBuilder->singleton('container.parameter.processors', ArrayIterator::class)
            ->addArgument($processorRefs)
            ->setPublic(true);
    }
}
