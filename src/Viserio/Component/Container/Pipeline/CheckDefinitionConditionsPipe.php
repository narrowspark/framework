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

use Viserio\Component\Container\Definition\ConditionDefinition;
use Viserio\Contract\Container\ContainerBuilder;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;
use Viserio\Contract\Container\Definition\PropertiesAwareDefinition as PropertiesAwareDefinitionContract;
use Viserio\Contract\Container\Pipe as PipeContract;

class CheckDefinitionConditionsPipe implements PipeContract
{
    public function process(ContainerBuilder $containerBuilder): void
    {
        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            if ($definition->getChange('condition')) {
                $conditions = $definition->getConditions();

                foreach ($conditions as $index => $conditionArgument) {
                    $conditionDefinition = new class() extends ConditionDefinition {
                    };

                    $conditionArgument->getCallback()($conditionDefinition);

                    if (! $definition instanceof PropertiesAwareDefinitionContract && ! $definition instanceof MethodCallsAwareDefinitionContract) {
                        unset($conditions[$index]);

                        $containerBuilder->log($this, \sprintf('Removed condition from [%s]; reason: Definition is missing implementation of [Viserio\Contract\Container\Definition\MethodCallsAwareDefinition] or [Viserio\Contract\Container\Definition\PropertiesAwareDefinition] interface.', $id));
                    }
                }

                $definition->setConditions($conditions);

                $containerBuilder->setDefinition($id, $definition);
            }
        }
    }
}
