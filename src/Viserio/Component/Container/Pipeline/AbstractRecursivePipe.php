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

use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Definition\ArrayDefinition;
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ArgumentAwareDefinition as ArgumentAwareDefinitionContract;
use Viserio\Contract\Container\Definition\Definition;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareContract;
use Viserio\Contract\Container\Definition\PropertiesAwareDefinition as PropertiesAwareDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Pipe as PipeContract;

abstract class AbstractRecursivePipe implements PipeContract
{
    /**
     * A container builder instance.
     *
     * @var \Viserio\Contract\Container\ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * The current used id.
     *
     * @var string
     */
    protected $currentId;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;

        try {
            $this->processValue($containerBuilder->getDefinitions(), true);
        } finally {
            $this->containerBuilder = null;
        }
    }

    /**
     * Processes a value found in a definition tree.
     *
     * @param mixed $value
     * @param bool  $isRoot
     *
     * @return mixed The processed value
     */
    protected function processValue($value, bool $isRoot = false)
    {
        if (\is_array($value)) {
            foreach ($value as $key => $v) {
                if ($key === ContainerInterface::class) {
                    continue;
                }

                if ($isRoot) {
                    $this->currentId = $key;
                }

                if ($v !== $processedValue = $this->processValue($v, $isRoot)) {
                    $value[$key] = $processedValue;
                }
            }

            return $value;
        }

        if ($value instanceof ArrayDefinition || $value instanceof ArgumentContract) {
            $value->setValue($this->processValue($value->getValue()));

            return $value;
        }

        if ($value instanceof Definition) {
            if ($value instanceof ArgumentAwareDefinitionContract && $value->getChange('arguments')) {
                $value->setArguments($this->processValue($value->getArguments()));
            }

            if ($value instanceof PropertiesAwareDefinitionContract && $value->getChange('properties')) {
                $value->setProperties($this->processValue($value->getProperties()));
            }

            if ($value instanceof MethodCallsAwareContract && $value->getChange('method_calls')) {
                $value->setMethodCalls($this->processValue($value->getMethodCalls()));
            }

            if ($value instanceof FactoryDefinitionContract) {
                $value->setValue($this->processValue($value->getValue()));

                if ($value->getChange('class_arguments')) {
                    $value->setClassArguments($this->processValue($value->getClassArguments()));
                }

                return $value;
            }
        } elseif ($value instanceof ReferenceDefinitionContract) {
            $methodCalls = [];

            foreach ($value->getMethodCalls() as $key => [$method, $parameters, $returnsClone]) {
                $methodCalls[$key] = [$method, $this->processValue($parameters), $returnsClone];
            }

            $value->setMethodCalls($methodCalls);
        }

        return $value;
    }
}
