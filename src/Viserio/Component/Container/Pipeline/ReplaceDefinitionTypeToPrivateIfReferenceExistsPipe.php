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
use Viserio\Component\Container\Definition\AbstractDefinition;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;

class ReplaceDefinitionTypeToPrivateIfReferenceExistsPipe extends AbstractRecursivePipe
{
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        $value = parent::processValue($value, $isRoot);

        if ($value instanceof ReferenceDefinitionContract && ($name = $value->getName()) !== ContainerInterface::class && $this->containerBuilder->hasDefinition($name)) {
            $definition = $this->containerBuilder->getDefinition($value->getName());

            if ($definition instanceof AbstractDefinition && ($definition->getType() === 1 /* Definition::SERVICE */ || $definition->getType() === 2 /* Definition::SINGLETON */)) {
                $definition->setType($definition->getType() + 3 /* Definition::PRIVATE */);
            }

            return $value;
        }

        return $value;
    }
}
