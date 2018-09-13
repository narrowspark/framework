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
     * @param \ReflectionClass $reflectionClass
     *
     * @return array
     */
    public function readConfig(ReflectionClass $reflectionClass): array
    {
        $interfaces = \array_flip($reflectionClass->getInterfaceNames());

        if (isset($interfaces[RequiresConfigContract::class])) {
            $dimensions       = [];
            $defaultOptions   = [];
            $key              = null;

            if (isset($interfaces[RequiresComponentConfigContract::class])) {
                $dimensions = $reflectionClass->getName()::getDimensions();
                $key        = \end($dimensions);
            }

            if (isset($interfaces[ProvidesDefaultOptionsContract::class])) {
                $defaultOptions = $reflectionClass->getName()::getDefaultOptions();
            }

            if (isset($interfaces[RequiresMandatoryOptionsContract::class])) {
                $config = \array_merge_recursive(
                    $defaultOptions,
                    $this->readMandatoryOption($reflectionClass->getName(), $dimensions, $reflectionClass->getName()::getMandatoryOptions())
                );
            } else {
                $config = $defaultOptions;
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
