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
    private $configResolverCache = [];

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof ConfigDefinition) {
            $class = $value->getClass();

            if (! isset($this->configResolverCache[$class])) {
                $interfaces = \class_implements($class);

                if (! \array_key_exists(RequiresComponentConfigContract::class, $interfaces)) {
                    throw new InvalidArgumentException('todo.');
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

                if (\array_key_exists(RequiresMandatoryConfigContract::class, $interfaces)) {
                    $iterator = new ConfigurationMandatoryIterator($class, $iterator);
                }

                if (\array_key_exists(RequiresValidatedConfigContract::class, $interfaces)) {
                    $iterator = new ConfigurationValidatorIterator($class, $iterator);
                }

                $default = [];

                if (\array_key_exists(ProvidesDefaultConfigContract::class, $interfaces)) {
                    $iterator = new ConfigurationDefaultIterator($class, $iterator);

                    $default = $class::getDefaultConfig();
                    $default instanceof Iterator ? \iterator_to_array($default) : (array) $default;
                }

                if (\array_key_exists(DeprecatedConfigContract::class, $interfaces)) {
                    new ConfigurationDeprecatedIterator($class, $iterator);
                }

                $objectDefinition = new ObjectDefinition(\sprintf('%s.config', $class), ConfigBag::class, 3 /* \Viserio\Contract\Container\Definition::PRIVATE */);
                $objectDefinition->addArgument($default);

                if (\count($dimensions) !== 0 && \iterator_count($parameters) !== 0) {
                    $objectDefinition->addArgument(\sprintf('{%s}', \implode('.', $dimensions)));
                }

                return $this->configResolverCache[$class] = $objectDefinition;
            }

            return $this->configResolverCache[$class];
        }

        return parent::processValue($value, $isRoot);
    }
}
