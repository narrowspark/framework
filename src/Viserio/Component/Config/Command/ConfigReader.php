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

namespace Viserio\Component\Config\Command;

use ReflectionClass;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;

class ConfigReader
{
    public function readConfig(ReflectionClass $reflectionClass): array
    {
        $interfaces = \array_flip($reflectionClass->getInterfaceNames());

        if (isset($interfaces[RequiresConfigContract::class])) {
            $dimensions = [];
            $defaultConfig = [];
            $key = null;
            $class = $reflectionClass->getName();

            if (isset($interfaces[RequiresComponentConfigContract::class])) {
                $dimensions = $class::getDimensions();
                $key = \end($dimensions);
            }

            if (isset($interfaces[ProvidesDefaultConfigContract::class])) {
                $defaultConfig = $class::getDefaultConfig();
            }

            if (isset($interfaces[RequiresMandatoryConfigContract::class])) {
                $config = \array_merge_recursive(
                    $defaultConfig,
                    $this->readMandatoryOption($class, $dimensions, $class::getMandatoryConfig())
                );
            } else {
                $config = $defaultConfig;
            }

            if (\count($dimensions) !== 0) {
                $config = $this->buildMultidimensionalArray($dimensions, $config);
            }

            if ($key !== null) {
                return [$key => $config];
            }

            return $config;
        }

        return [];
    }

    /**
     * Read the mandatory config.
     */
    protected function readMandatoryOption(string $className, array $dimensions, array $mandatoryConfig): array
    {
        $config = [];

        foreach ($mandatoryConfig as $key => $mandatoryOption) {
            if (! \is_scalar($mandatoryOption)) {
                $config[$key] = $this->readMandatoryOption($className, $dimensions, $mandatoryConfig[$key]);

                continue;
            }

            $config[$mandatoryOption] = null;
        }

        return $config;
    }

    /**
     * Builds a multidimensional config array.
     */
    private function buildMultidimensionalArray(array $dimensions, $value): array
    {
        $config = [];
        $index = \array_shift($dimensions);

        if (! isset($dimensions[0])) {
            $config[$index] = $value;
        } else {
            $config[$index] = $this->buildMultidimensionalArray($dimensions, $value);
        }

        return $config;
    }
}
