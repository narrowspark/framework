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

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;
use Viserio\Contract\Container\Pipe as PipeContract;

/**
 * @internal
 */
final class RemoveUninitializedReferencesInMethodCallsPipe implements PipeContract
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            if (! $definition instanceof MethodCallsAwareDefinitionContract) {
                continue;
            }

            $methodCalls = $definition->getMethodCalls();

            foreach ($definition->getMethodCalls() as $key => $methodCall) {
                [$methodName, $methodValue] = $methodCall;

                $hasNotFoundServices = false;
                $services = [];

                foreach (ContainerBuilder::getInitializedConditionals($methodValue) as $service) {
                    $services[] = $service;

                    if (! $containerBuilder->hasDefinition($service)) {
                        $hasNotFoundServices = true;
                    }
                }

                if ($hasNotFoundServices) {
                    unset($methodCalls[$key]);

                    $containerBuilder->log($this, \sprintf('The method call [%s] for definition [%s] was removed because needed service%s [\'%s\'] %s not found.', $methodName, $definition->getName(), \count($services) !== 1 ? 's' : '', \implode('\', \'', $services), \count($services) !== 1 ? 'were' : 'was'));
                }
            }

            $definition->setMethodCalls($methodCalls);
        }
    }
}
