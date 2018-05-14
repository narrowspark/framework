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
use Viserio\Contract\Container\Definition\ArgumentAwareDefinition as ArgumentAwareDefinitionContract;
use Viserio\Contract\Container\Definition\AutowiredAwareDefinition as AutowiredAwareDefinitionContract;
use Viserio\Contract\Container\Definition\DecoratorAwareDefinition as DecoratorAwareDefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;
use Viserio\Contract\Container\Definition\PropertiesAwareDefinition as PropertiesAwareDefinitionContract;
use Viserio\Contract\Container\Definition\TagAwareDefinition as TagAwareDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;

class ResolveUndefinedDefinitionPipe extends AbstractRecursivePipe
{
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if (! $value instanceof UndefinedDefinitionContract) {
            return parent::processValue($value, $isRoot);
        }

        /** @var \Viserio\Contract\Container\Definition\Definition $definition */
        $definition = ContainerBuilder::createDefinition($value->getName(), $value->getValue(), $value->getType(), true);

        $definition->setPublic($value->isPublic());
        $definition->setSynthetic($value->isSynthetic());
        $definition->setLazy($value->isLazy());

        if ($definition instanceof PropertiesAwareDefinitionContract && $value->getChange('properties')) {
            $properties = [];

            foreach ($value->getProperties() as $key => [$v, $static]) {
                $properties[$key] = [$this->processValue($v), $static];
            }

            $value->setProperties($properties);
        }

        if ($definition instanceof AutowiredAwareDefinitionContract && $value->getChange('autowired')) {
            $definition->setAutowired($value->isAutowired());
        }

        if ($definition instanceof MethodCallsAwareDefinitionContract && $value->getChange('method_calls')) {
            $definition->setMethodCalls($value->getMethodCalls());
        }

        if ($definition instanceof TagAwareDefinitionContract && $value->getChange('tags')) {
            $definition->setTags($value->getTags());
        }

        if ($definition instanceof ArgumentAwareDefinitionContract && $value->getChange('arguments')) {
            $definition->setArguments($value->getArguments());
        }

        if ($definition instanceof FactoryDefinitionContract && $value->getChange('class_arguments')) {
            $definition->setClassArguments($value->getClassArguments());
        }

        if ($definition instanceof DecoratorAwareDefinitionContract && $value->getChange('decorated_service')) {
            [$id, $renamedId, $priority] = $value->getDecorator();

            $definition->decorate($id, $renamedId, $priority);
        }

        $definition->setDeprecated($value->isDeprecated());

        $this->containerBuilder->setDefinition($definition->getName(), $definition);

        return $definition;
    }
}
