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

namespace Viserio\Component\OptionsResolver\Container\Pipeline;

use Viserio\Component\Container\Pipeline\AbstractRecursivePipe;
use Viserio\Component\OptionsResolver\Container\Definition\DimensionsOptionDefinition;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Contract\Container\Exception\NotFoundException;

class ResolveOptionDefinitionPipe extends AbstractRecursivePipe
{
    /** @var string */
    private $configServiceId;

    /** @var array */
    private static $configResolverCache = [];

    /**
     * Create a new ResolveOptionDefinition instance.
     *
     * @param string $configServiceId
     */
    public function __construct(string $configServiceId = 'config')
    {
        $this->configServiceId = $configServiceId;
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if (! $this->containerBuilder->has($this->configServiceId)) {
            throw new NotFoundException($this->configServiceId);
        }

        $isOptionDefinition = $value instanceof OptionDefinition;
        $isDimensionsOptionDefinition = $value instanceof DimensionsOptionDefinition;

        if ($isOptionDefinition || $isDimensionsOptionDefinition) {
            $configClass = $value::$configClass = $value->getClass();

            if ($isDimensionsOptionDefinition) {
                /** @var DimensionsOptionDefinition $value */
                $value::$classDimensions = $value->getClassDimensions();
            }

            if (! isset(self::$configResolverCache[$configClass])) {
                $config = $this->containerBuilder->findDefinition($this->configServiceId)->getValue();

                $value->setConfig($config);

                $array = self::$configResolverCache[$configClass] = $value->getValue();
            } else {
                $array = self::$configResolverCache[$configClass];
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
