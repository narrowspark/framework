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
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Pipe as PipeContract;
use Viserio\Contract\Container\Processor\ParameterProcessor as ParameterProcessorContract;

final class RegisterParameterProcessorsPipe implements PipeContract
{
    /** @var string */
    public const TAG = 'viserio.container.parameter.processor';

    /** @var string */
    public const PROCESSORS_KEY = 'viserio.container.parameter.processors';

    /** @var string */
    public const PROCESSOR_TYPES_PARAMETER_KEY = 'viserio.container.parameter.processor.types';

    /** @var string */
    public const RUNTIME_PROCESSORS_KEY = 'viserio.container.runtime.parameter.processors';

    /** @var string */
    public const RUNTIME_PROCESSOR_TYPES_PARAMETER_KEY = 'viserio.container.runtime.parameter.processor.types';

    /**
     * List of allowed types.
     *
     * @var array<int, string>
     */
    private static array $allowedTypes = ['array', 'bool', 'float', 'int', 'string'];

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
        $runtimeProcessorRefs = [];
        $runtimeProcessorTypes = [];

        $processorRefs = [];
        $processorTypes = [];

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
                throw new InvalidArgumentException(\sprintf('The service [%s] tagged with [%s] must implement interface [%s].', $id, $this->tag, ParameterProcessorContract::class));
            }

            $isRuntimeProcessor = $class::isRuntime();

            foreach ($class::getProvidedTypes() as $key => $type) {
                self::validateProvidedTypes($type, $class);

                $types = \explode('|', $type);

                $runtimeProcessorTypes[$key] = $types;

                if (! $isRuntimeProcessor) {
                    $processorTypes[$key] = $types;
                }
            }

            $reference = (new ReferenceDefinition($id))
                ->setType($class);

            $runtimeProcessorRefs[] = $reference;

            if (! $isRuntimeProcessor) {
                $processorRefs[] = $reference;
            }
        }

        if (\count($runtimeProcessorRefs) !== 0) {
            /** @var \Viserio\Contract\Container\Definition\IteratorDefinition $iteratorDefinition */
            $iteratorDefinition = $containerBuilder->singleton(self::RUNTIME_PROCESSORS_KEY, ArrayIterator::class);
            $iteratorDefinition
                ->setArgument($runtimeProcessorRefs)
                ->addTag(ResolvePreloadPipe::TAG)
                ->setPublic(true);
            $containerBuilder->setParameter(self::RUNTIME_PROCESSOR_TYPES_PARAMETER_KEY, $runtimeProcessorTypes);
        }

        if (\count($processorRefs) !== 0) {
            /** @var \Viserio\Contract\Container\Definition\IteratorDefinition $iteratorDefinition */
            $iteratorDefinition = $containerBuilder->singleton(self::PROCESSORS_KEY, ArrayIterator::class);
            $iteratorDefinition->setArgument($processorRefs);

            $containerBuilder->setParameter(self::PROCESSOR_TYPES_PARAMETER_KEY, $processorTypes);
        }
    }

    private static function validateProvidedTypes(string $types, string $class): array
    {
        $types = \explode('|', $types);

        foreach ($types as $type) {
            if (! \in_array($type, self::$allowedTypes, true)) {
                throw new InvalidArgumentException(\sprintf('Invalid type [%s] returned by [%s::getProvidedTypes()], expected one of [%s].', $type, $class, \implode('", "', self::$allowedTypes)));
            }
        }

        return $types;
    }
}
