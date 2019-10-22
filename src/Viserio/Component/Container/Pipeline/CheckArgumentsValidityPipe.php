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

use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\RuntimeException;

class CheckArgumentsValidityPipe extends AbstractRecursivePipe
{
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if (! $value instanceof ObjectDefinitionContract && ! $value instanceof FactoryDefinitionContract && ! $value instanceof ClosureDefinitionContract) {
            return parent::processValue($value, $isRoot);
        }

        $i = 0;

        foreach ($value->getArguments() as $k => $v) {
            if ($k !== $i++) {
                if (! \is_int($k)) {
                    throw new RuntimeException(\sprintf('Invalid %s argument for service [%s]: integer expected but found string [%s]. Check your service definition.', $value instanceof FactoryDefinitionContract ? 'method' : 'constructor', $this->currentId, $k));
                }

                throw new RuntimeException(\sprintf('Invalid %s argument [%d] for service [%s]: argument [%d] must be defined before. Check your service definition.', $value instanceof FactoryDefinitionContract ? 'method' : 'constructor', 1 + $k, $this->currentId, $i));
            }
        }

        if ($value instanceof FactoryDefinitionContract) {
            $i = 0;

            foreach ($value->getClassArguments() as $k => $v) {
                if ($k !== $i++) {
                    if (! \is_int($k)) {
                        throw new RuntimeException(\sprintf('Invalid constructor argument for service [%s]: integer expected but found string [%s]. Check your service definition.', $this->currentId, $k));
                    }

                    throw new RuntimeException(\sprintf('Invalid constructor argument [%d] for service [%s]: argument [%d] must be defined before. Check your service definition.', 1 + $k, $this->currentId, $i));
                }
            }
        } elseif ($value instanceof ObjectDefinitionContract) {
            foreach ($value->getMethodCalls() as $methodCall) {
                $i = 0;

                foreach ($methodCall[1] as $k => $v) {
                    if ($k !== $i++) {
                        if (! \is_int($k)) {
                            throw new RuntimeException(\sprintf('Invalid argument for method call [%s] of service [%s]: integer expected but found string [%s]. Check your service definition.', $methodCall[0], $this->currentId, $k));
                        }

                        throw new RuntimeException(\sprintf('Invalid argument [%d] for method call [%s] of service [%s]: argument [%d] must be defined before. Check your service definition.', 1 + $k, $methodCall[0], $this->currentId, $i));
                    }
                }
            }
        }
    }
}
