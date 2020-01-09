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

namespace Viserio\Component\Container\Pipeline;

use ArrayIterator;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Pipe as PipeContract;
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
        $registeredTypes = [];

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

            $containerBuilder->setDefinition($id, $definition);

            foreach (\array_keys($class::getProvidedTypes()) as $key) {
                $registeredTypes[$key] = true;
            }

            $processorRefs[] = $definition;
        }

        if (\count($processorRefs) !== 0) {
            $containerBuilder->singleton('container.parameter.processors', new ArrayIterator($processorRefs))
                ->setPublic(true);
            $containerBuilder->setParameter('container.parameter.provided.processor.types', $registeredTypes);
        }
    }
}
