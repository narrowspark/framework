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
use EmptyIterator;
use Iterator;
use Viserio\Component\Config\ConfigurationDefaultIterator;
use Viserio\Component\Config\ConfigurationDeprecatedIterator;
use Viserio\Component\Config\ConfigurationDimensionsIterator;
use Viserio\Component\Config\ConfigurationMandatoryIterator;
use Viserio\Component\Config\ConfigurationValidatorIterator;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Container\Argument\ParameterArgument;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Pipeline\AbstractRecursivePipe;
use Viserio\Contract\Config\DeprecatedConfig as DeprecatedConfigContract;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

class ResolveConfigDefinitionPipe extends AbstractRecursivePipe
{
    /** @var array */
    private array $configResolverCache = [];

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof ConfigDefinition) {
            $class = $value->getClass();
            $configKey = $value->getKey();
            $indexKey = $class . ($configKey ?? '');

            if (! \array_key_exists($indexKey, $this->configResolverCache)) {
                if (! $r = $this->containerBuilder->getClassReflector($class)) {
                    throw new InvalidArgumentException(\sprintf('Class [%s] used for config definition in service [%s] cannot be found.', $class, $this->currentId));
                }

                if (! $r->implementsInterface(RequiresComponentConfigContract::class)) {
                    throw new InvalidArgumentException(\sprintf('Class [%s] is missing implementation of [%s] interface in service [%s]', $class, RequiresComponentConfigContract::class, $this->currentId));
                }

                $dimensions = $class::getDimensions();
                $dimensions = $dimensions instanceof Iterator ? \iterator_to_array($dimensions) : (array) $dimensions;
                $parameters = new EmptyIterator();

                $key = $dimensions[\array_key_first($dimensions)];

                if ($this->containerBuilder->hasParameter($key)) {
                    $parameters = $this->containerBuilder->getParameter($key)->getValue();
                    $parameters = $parameters instanceof Iterator ? $parameters : new ArrayIterator([$key => $parameters]);
                } else {
                    foreach ($this->containerBuilder->getParameters() as $id => $parameter) {
                        if ($id === $key) {
                            $parameters = [$key => $parameter->getValue()];

                            break;
                        }
                    }
                }

                if ($parameters instanceof EmptyIterator) {
                    $this->containerBuilder->log($this, \sprintf('Using the first key [%s] of the config dimensions failed to get parameter for [%s].', $key, $this->currentId));
                }

                $iterator = new ConfigurationDimensionsIterator($class, $parameters, $value->getId());

                if ($r->implementsInterface(RequiresMandatoryConfigContract::class)) {
                    $iterator = new ConfigurationMandatoryIterator($class, $iterator);
                }

                if ($r->implementsInterface(RequiresValidatedConfigContract::class)) {
                    $iterator = new ConfigurationValidatorIterator($class, $iterator);
                }

                $default = [];

                if ($r->implementsInterface(ProvidesDefaultConfigContract::class)) {
                    $iterator = new ConfigurationDefaultIterator($class, $iterator);

                    $default = $class::getDefaultConfig();
                    $default instanceof Iterator ? \iterator_to_array($default) : (array) $default;
                }

                if ($r->implementsInterface(DeprecatedConfigContract::class)) {
                    new ConfigurationDeprecatedIterator($class, $iterator);
                }

                if ($configKey === null) {
                    $definition = new ObjectDefinition(\sprintf('%s.config', $class), ConfigBag::class, 3 /* \Viserio\Contract\Container\Definition::PRIVATE */);
                    $definition->addArgument($default);

                    if (\count($dimensions) !== 0 && \iterator_count($parameters) !== 0) {
                        $definition->addArgument(\sprintf('{%s}', \implode('.', $dimensions)));
                    }

                    return $this->configResolverCache[$indexKey] = $definition;
                }

                $expression = \sprintf('{%s%s}', \implode('.', $dimensions), '.' . $configKey);

                if (\count($default) !== 0) {
                    $hasDefaultValue = true;

                    foreach (\explode('.', $configKey) as $segment) {
                        if (\is_array($default) && (\array_key_exists($segment, $default) || isset($default[$segment]))) {
                            $default = $default[$segment];
                        } else {
                            $hasDefaultValue = false;

                            break;
                        }
                    }

                    if ($hasDefaultValue) {
                        $this->containerBuilder->log($this, \sprintf('No parameter value was found for [%s], falling back to default value found in [%s].', $expression, $class));

                        return $this->configResolverCache[$indexKey] = new ParameterArgument($expression, $default);
                    }
                }

                return $this->configResolverCache[$indexKey] = $expression;
            }

            return $this->configResolverCache[$indexKey];
        }

        return parent::processValue($value, $isRoot);
    }
}
