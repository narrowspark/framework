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

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;

class ResolveFactoryClassPipe extends AbstractRecursivePipe
{
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof FactoryDefinitionContract) {
            [$class, $method] = $value->getValue();

            if (\is_object($class)) {
                $class = \get_class($class);
            }

            if (! $class instanceof ReferenceDefinitionContract && $this->containerBuilder->has($class)) {
                /** @var ObjectDefinitionContract $foundDefinition */
                $foundDefinition = $this->containerBuilder->findDefinition($class);

                $value->setValue([(new ReferenceDefinition($foundDefinition->getName()))->setType($foundDefinition->getClass()), $method]);
            }
        }

        return parent::processValue($value, $isRoot);
    }
}
