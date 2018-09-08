<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Command;

use ReflectionClass;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class OptionsReader
{
    /**
     * @param array  $configs
     * @param string $className
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    public function readConfig(array $configs, string $className): array
    {
        $reflectionClass = new ReflectionClass($className);

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

    /**
     * Read the mandatory options.
     *
     * @param string $className
     * @param array  $dimensions
     * @param array  $mandatoryOptions
     *
     * @return array
     */
    protected function readMandatoryOption(string $className, array $dimensions, array $mandatoryOptions): array
    {
        $options = [];

        foreach ($mandatoryOptions as $key => $mandatoryOption) {
            if (! \is_scalar($mandatoryOption)) {
                $options[$key] = $this->readMandatoryOption($className, $dimensions, $mandatoryOptions[$key]);

                continue;
            }

            $options[$mandatoryOption] = null;
        }

        return $options;
    }

    /**
     * Builds a multidimensional config array.
     *
     * @param array $dimensions
     * @param mixed $value
     *
     * @return array
     */
    private function buildMultidimensionalArray(array $dimensions, $value): array
    {
        $config = [];
        $index  = \array_shift($dimensions);

        if (! isset($dimensions[0])) {
            $config[$index] = $value;
        } else {
            $config[$index] = $this->buildMultidimensionalArray($dimensions, $value);
        }

        return $config;
    }
}