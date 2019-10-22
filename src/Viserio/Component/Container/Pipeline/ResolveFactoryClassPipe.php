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
