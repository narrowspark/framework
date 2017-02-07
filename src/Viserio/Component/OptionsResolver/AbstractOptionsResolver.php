<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\Resolver as ResolverContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;

abstract class AbstractOptionsResolver implements ResolverContract
{
    use ContainerAwareTrait;

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
     * Checks if a mandatory param is missing, supports recursion.
     *
     * @param iterable $mandatoryOptions
     * @param iterable $config
     *
     * @throws MandatoryOptionNotFoundException
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

            throw MandatoryOptionNotFoundException::missingOption(
                $this->configClass->getDimensions(),
                $useRecursion ? $key : $mandatoryOption
            );
        }
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
     * Get configuration.
     *
     * @param \Interop\Container\ContainerInterface|\ArrayAccess|array $data
     *
     * @throws \RuntimeException
     *
     * @return array|\ArrayAccess
     */
    protected function resolveConfiguration($data)
    {
        if ($data instanceof ContainerInterface) {
            if ($data->has(RepositoryContract::class)) {
                return $data->get(RepositoryContract::class);
            } elseif ($data->has('config')) {
                return $data->get('config');
            } elseif ($data->has('options')) {
                return $data->get('options');
            }
        } elseif ($data instanceof ArrayAccess || is_array($data)) {
            return $data;
        }

        throw new RuntimeException('No configuration found.');
    }
}
