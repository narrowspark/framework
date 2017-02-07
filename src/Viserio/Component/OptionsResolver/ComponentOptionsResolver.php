<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

class ComponentOptionsResolver extends AbstractOptionsResolver
{
    /**
     * Checks if options are available depending on implemented interfaces and checks that the retrieved options from
     * the dimensions path are an array or have implemented \ArrayAccess. The RequiresConfigId interface is supported.
     *
     * `canRetrieveOptions()` returning true does not mean that `options($config)` will not throw an exception.
     * It does however mean that `options()` will not throw an `OptionNotFoundException`. Mandatory options are
     * not checked.
     *
     * @param iterable    $config   Configuration
     * @param string|null $configId Config name, must be provided if factory uses RequiresConfigId interface
     *
     * @return bool True if options depending on dimensions are available, otherwise false
     */
    protected function canRetrieveOptions(iterable $config, string $configId = null): bool
    {
        $dimensions = $this->configClass->getDimensions();
        $dimensions = $dimensions instanceof Iterator ? iterator_to_array($dimensions) : $dimensions;

        if ($this->configClass instanceof RequiresConfigIdContract) {
            $dimensions[] = $configId;
        }

        foreach ($dimensions as $dimension) {
            if (((array) $config !== $config && ! $config instanceof ArrayAccess)
                || (! isset($config[$dimension]) && $this->configClass instanceof RequiresMandatoryOptionsContract)
                || (! isset($config[$dimension]) && ! $this->configClass instanceof ProvidesDefaultOptionsContract)
            ) {
                return false;
            }

            if ($this->configClass instanceof ProvidesDefaultOptionsContract && ! isset($config[$dimension])) {
                return true;
            }

            $config = $config[$dimension];
        }

        return (array) $config === $config || $config instanceof ArrayAccess;
    }
}
