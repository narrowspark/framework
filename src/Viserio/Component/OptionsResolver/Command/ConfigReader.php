<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use ReflectionClass;
use ReflectionException;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConfigReader
{
    public function readConfig(string $class): array
    {
        $configs = [];

        try {
            $reflectionClass = new ReflectionClass($className);
        } catch (ReflectionException $e) {
            continue;
        }

        $interfaces = \array_flip($reflectionClass->getInterfaceNames());

        if (isset($interfaces[RequiresConfigContract::class]) && ! $reflectionClass->isInternal() && ! $reflectionClass->isAbstract()) {
            $dimensions       = [];
            $mandatoryOptions = [];
            $defaultOptions   = [];
            $key              = null;

            if (isset($interfaces[RequiresComponentConfigContract::class])) {
                $dimensions = $className::getDimensions();
                $key        = \end($dimensions);
            }

            if (isset($interfaces[ProvidesDefaultOptionsContract::class])) {
                $defaultOptions = $className::getDefaultOptions();
            }

            if (isset($interfaces[RequiresMandatoryOptionsContract::class])) {
                $mandatoryOptions = $this->readMandatoryOption($className, $dimensions, $className::getMandatoryOptions());
            }

            $options = \array_merge_recursive($defaultOptions, $mandatoryOptions);
            $config  = $this->buildMultidimensionalArray($dimensions, $options);

            if ($key !== null && isset($configs[$key])) {
                $config = \array_replace_recursive($configs[$key], $config);
            }

            $configs[$key] = $config;
        }

        return $configs;
    }
}