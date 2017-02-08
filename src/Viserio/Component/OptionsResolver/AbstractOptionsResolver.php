<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\Resolver as ResolverContract;

abstract class AbstractOptionsResolver implements ResolverContract
{
    /**
     * Checks if a mandatory param is missing, supports recursion.
     *
     * @param iterable $mandatoryOptions
     * @param iterable $config
     *
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException
     */
    protected function checkMandatoryOptions(iterable $mandatoryOptions, iterable $config): void
    {
        foreach ($mandatoryOptions as $key => $mandatoryOption) {
            $useRecursion = ! is_scalar($mandatoryOption);

            if (! $useRecursion && isset($config[$mandatoryOption])) {
                continue;
            }

            if ($useRecursion && isset($config[$key])) {
                $this->checkMandatoryOptions($mandatoryOption, $config[$key]);

                return;
            }

            $configClass = $this->getConfigurableClass();

            throw new MandatoryOptionNotFoundException(
                $configClass instanceof RequiresComponentConfigContract ? $configClass->getDimensions() : [],
                $useRecursion ? $key : $mandatoryOption
            );
        }
    }

    /**
     * Checks if options are available depending on implemented interfaces and checks that the retrieved options
     * are an array or have implemented \ArrayAccess. The RequiresConfigId interface is supported.
     *
     * `canRetrieveOptions()` returning true does not mean that `resolve($config)` will not throw an exception.
     * It does however mean that `resolve()` will not throw an `OptionNotFoundException`. Mandatory options are
     * not checked.
     *
     * @param iterable    $config   Configuration
     * @param string|null $configId Config name, must be provided if factory uses RequiresConfigId interface
     *
     * @return bool True if options are available, otherwise false
     */
    abstract protected function canRetrieveOptions(iterable $config, string $configId = null): bool;

    /**
     * Get resolve the right configuration data.
     *
     * @param \Interop\Container\ContainerInterface|\ArrayAccess|array $data
     *
     * @throws \RuntimeException
     *
     * @return array|\ArrayAccess
     */
    abstract protected function resolveConfiguration($data);

    /**
     * Returns a configurable class.
     *
     * @return \Viserio\Component\Contracts\OptionsResolver\RequiresConfig
     */
    abstract protected function getConfigurableClass();
}
