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

namespace Viserio\Component\Container\Pipeline;

use Viserio\Component\Container\Argument\ClosureArgument;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Exception\NotFoundException;
use Viserio\Contract\Container\Exception\RuntimeException;
use Viserio\Contract\Container\Pipe as PipeContract;

/**
 * @internal
 */
final class ResolveInvalidReferencesPipe implements PipeContract
{
    /**
     * A container builder instance.
     *
     * @var \Viserio\Contract\Container\ContainerBuilder
     */
    private $containerBuilder;

    /**
     * The current used id.
     *
     * @var int|string
     */
    private $currentId;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;

        try {
            foreach ($containerBuilder->getDefinitions() as $this->currentId => $definition) {
                $this->processValue($definition);
            }
        } finally {
            $this->containerBuilder = $this->currentId = null;
        }
    }

    /**
     * Processes arguments to determine invalid references.
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException When a service is not found
     * @throws \Viserio\Contract\Container\Exception\RuntimeException When an invalid reference is found
     */
    private function processValue($value, int $rootLevel = 0, int $level = 0)
    {
        if ($value instanceof DefinitionContract && $value->isSynthetic()) {
            return $value;
        }

        if ($value instanceof ClosureArgument) {
            $value->setValue($this->processValue($value->getValue(), 1, 1));

            return $value;
        }

        if ($value instanceof ArgumentContract) {
            $value->setValue($this->processValue($value->getValue(), $rootLevel, 1 + $level));

            return $value;
        }

        if ($value instanceof ObjectDefinitionContract || $value instanceof FactoryDefinitionContract || $value instanceof ClosureDefinitionContract) {
            $value->setArguments($this->processValue($value->getArguments()));
        }

        if ($value instanceof FactoryDefinitionContract) {
            $value->setClassArguments($this->processValue($value->getClassArguments(), 1));
        }

        if ($value instanceof ParameterDefinition) {
            $value->setValue($this->processValue($value->getValue(), $rootLevel, 1 + $level));
        } elseif ($value instanceof ObjectDefinitionContract || $value instanceof FactoryDefinitionContract) {
            $value->setProperties($this->processValue($value->getProperties(), 1));
            $value->setMethodCalls($this->processValue($value->getMethodCalls(), 2));
        } elseif (\is_array($value)) {
            $i = 0;

            foreach ($value as $k => $v) {
                try {
                    if ($i !== false && $k !== $i++) {
                        $i = false;
                    }

                    if ($v !== $processedValue = $this->processValue($v, $rootLevel, 1 + $level)) {
                        $value[$k] = $processedValue;
                    }
                } catch (RuntimeException $exception) {
                    if ($rootLevel < $level || ($rootLevel >= 1 && $level === 0)) {
                        unset($value[$k]);

                        $this->containerBuilder->log($this, $exception->getMessage());
                    } elseif ($rootLevel >= 1) {
                        throw $exception;
                    } else {
                        $value[$k] = null;
                    }
                }
            }

            // Ensure numerically indexed arguments have sequential numeric keys.
            if ($i !== false) {
                $value = \array_values($value);
            }
        } elseif ($value instanceof ReferenceDefinitionContract) {
            if ($this->containerBuilder->hasDefinition($id = $value->getName())) {
                return $value;
            }

            $currentDefinition = $this->containerBuilder->getDefinition($this->currentId);

            // resolve decorated service behavior depending on decorator service
            if ($currentDefinition->innerServiceId === $id && $currentDefinition->decorationOnInvalid === 2/* ReferenceDefinitionContract::NULL_ON_INVALID_REFERENCE */) {
                return null;
            }

            $behavior = $value->getBehavior();

            if ($behavior === 0/* ReferenceDefinitionContract::RUNTIME_EXCEPTION_ON_INVALID_REFERENCE */ && ! $this->containerBuilder->hasDefinition($id)) {
                throw new NotFoundException($id, \is_int($this->currentId) ? null : $this->currentId);
            }

            // resolve invalid behavior
            if ($behavior === 2/* ReferenceDefinitionContract::NULL_ON_INVALID_REFERENCE */) {
                $value = null;
            } elseif ($behavior === 4/* ReferenceDefinitionContract::IGNORE_ON_INVALID_REFERENCE */) {
                if (0 < $level || $rootLevel >= 1) {
                    throw new RuntimeException(\sprintf('Removed invalid reference for [%s].', $id));
                }

                $value = null;
            }
        }

        return $value;
    }
}
