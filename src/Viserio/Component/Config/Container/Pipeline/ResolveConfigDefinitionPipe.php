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

use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Config\Container\Definition\DimensionsConfigDefinition;
use Viserio\Component\Container\Pipeline\AbstractRecursivePipe;

class ResolveConfigDefinitionPipe extends AbstractRecursivePipe
{
    /** @var array */
    private static $configResolverCache = [];

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        $isOptionDefinition = $value instanceof ConfigDefinition;
        $isDimensionsOptionDefinition = $value instanceof DimensionsConfigDefinition;

        if ($isOptionDefinition || $isDimensionsOptionDefinition) {
            $value::$configClass = $value->getClass();

            /** @var ConfigDefinition|DimensionsConfigDefinition $value */
            $value::$classDimensions = $value->getClassDimensions();

            if (! isset(self::$configResolverCache[$value::$configClass])) {
                /** @var string $firstKey */
                $firstKey = $value::$classDimensions[\array_key_first($value::$classDimensions)];

                $value->setConfig([$firstKey => $this->containerBuilder->getParameter($firstKey)->getValue()]);

                $array = self::$configResolverCache[$value::$configClass] = $value->getValue();
            } else {
                $array = self::$configResolverCache[$value::$configClass];
            }

            if ($isOptionDefinition) {
                foreach (\explode('.', $value->getName()) as $segment) {
                    if (! \array_key_exists($segment, $array)) {
                        continue;
                    }

                    $array = $array[$segment];
                }
            }

            return $array;
        }

        return parent::processValue($value, $isRoot);
    }
}
