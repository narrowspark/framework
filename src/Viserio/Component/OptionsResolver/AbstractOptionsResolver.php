<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\Resolver as ResolverContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;

abstract class AbstractOptionsResolver implements ResolverContract
{
    /**
     * Config class.
     *
     * @var \Viserio\Component\Contracts\OptionsResolver\RequiresConfig
     */
    protected $configClass;

    /**
     * Create a new file options resolver instance.
     *
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $configClass
     */
    public function __construct(RequiresConfigContract $configClass)
    {
        $this->configClass = $configClass;
    }

    /**
     * Checks if options can be retrieved from config and if not, default options (ProvidesDefaultOptions interface) or
     * an empty array will be returned.
     *
     * @param iterable    $config
     * @param string|null $configId Config name, must be provided if factory uses RequiresConfigId interface
     *
     * @return array options Default options or an empty array
     */
    protected function getFallbackOptions(iterable $config, ?string $configId = null): array
    {
        $options = [];

        if ($this->canRetrieveOptions($config, $configId)) {
            $options = $this->options($config, $configId);
        }

        if (empty($options) && $this instanceof ProvidesDefaultOptionsContract) {
            $options = $this->configClass->getDefaultOptions();
        }

        return $options;
    }

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
