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
use Viserio\Contract\Config\Processor\ParameterProcessor as ConfigParameterProcessorContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Pipe as PipeContract;
use Viserio\Contract\Container\Processor\ParameterProcessor as ParameterProcessorContract;

class RegisterParameterProcessorsPipe implements PipeContract
{
    /** @var string */
    public const TAG = 'container.parameter.processor';

    /**
     * List of allowed types.
     *
     * @var array<int, string>
     */
    private static $allowedTypes = ['array', 'bool', 'float', 'int', 'string'];

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

            if (! $r->implementsInterface(ParameterProcessorContract::class) && ! $r->implementsInterface(ConfigParameterProcessorContract::class)) {
                $configInterfaceMessage = \interface_exists(ConfigParameterProcessorContract::class) ? \sprintf(' or [%s]', ConfigParameterProcessorContract::class) : '';

                throw new InvalidArgumentException(\sprintf('The service [%s] tagged with [%s] must implement interface [%s]%s.', $id, $this->tag, ParameterProcessorContract::class, $configInterfaceMessage));
            }

            $containerBuilder->setDefinition($id, $definition);

            foreach ($class::getProvidedTypes() as $key => $type) {
                self::validateProvidedTypes($type, $class);

                $registeredTypes[$key] = \explode('|', $type);
            }

            if ($definition->getChange('method_calls') || $definition->getChange('properties') || $definition->getChange('decorated_service') || $definition->getChange('arguments')) {
                $definition->setPublic(true);
                $processorRefs[] = (new ReferenceDefinition($id))->setType($class);
            } else {
                $processorRefs[] = $definition;
            }
        }

        if (\count($processorRefs) !== 0) {
            $containerBuilder->singleton('container.parameter.processors', new ArrayIterator($processorRefs))
                ->setPublic(true);
            $containerBuilder->setParameter('container.parameter.provided.processor.types', $registeredTypes);
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
