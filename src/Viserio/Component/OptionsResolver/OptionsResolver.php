<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use ArrayAccess;
use Iterator;
use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\OptionsResolver\Resolver as ResolverContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;

class OptionsResolver implements ResolverContract
{
    use ContainerAwareTrait;

    /**
     * Config class.
     *
     * @var \Viserio\Component\Contracts\OptionsResolver\RequiresConfig
     */
    protected $configClass;

    /**
     * Create a new file view loader instance.
     *
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $configClass
     */
    public function __construct(RequiresConfigContract $configClass)
    {
        $this->configClass = $configClass;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(?iterable $config = null, string $configId = null): iterable
    {
        $config = $this->contaienr !== null && $config === null ? $this->configureOptions($this->getContainer()) : $config;
        $dimensions = $this->configClass->getDimensions();
        $dimensions = $dimensions instanceof Iterator ? iterator_to_array($dimensions) : $dimensions;

        if ($this->configClass instanceof RequiresConfigIdContract) {
            $dimensions[] = $configId;
        } elseif ($configId !== null) {
            throw new InvalidArgumentException(
                sprintf('The factory "%s" does not support multiple instances.', __CLASS__)
            );
        }

        // get configuration for provided dimensions
        foreach ($dimensions as $dimension) {
            if ((array) $config !== $config && !$config instanceof ArrayAccess) {
                throw UnexpectedValueException::invalidOptions($dimensions, $dimension);
            }

            if (!isset($config[$dimension])) {
                if (! $this->configClass instanceof RequiresMandatoryOptions && $this->configClass instanceof ProvidesDefaultOptions) {
                    break;
                }

                throw OptionNotFoundException::missingOptions($this, $dimension, $configId);
            }

            $config = $config[$dimension];
        }

        if ((array) $config !== $config && !$config instanceof ArrayAccess) {
            throw UnexpectedValueException::invalidOptions($this->configClass->getDimensions());
        }

        if ($this->configClass instanceof RequiresMandatoryOptions) {
            $this->checkMandatoryOptions($this->configClass->getMandatoryOptions(), $config);
        }

        if ($this->configClass instanceof ProvidesDefaultOptions) {
            $options = $this->configClass->getDefaultOptions();
            $config = array_replace_recursive(
                $options instanceof Iterator ? iterator_to_array($options) : (array) $options,
                (array) $config
            );
        }

        return $config;
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
    public function canRetrieveOptions(iterable $config, string $configId = null): bool
    {
        $dimensions = $this->configClass->getDimensions();
        $dimensions = $dimensions instanceof Iterator ? iterator_to_array($dimensions) : $dimensions;

        if ($this->configClass instanceof RequiresConfigIdContract) {
            $dimensions[] = $configId;
        }

        foreach ($dimensions as $dimension) {
            if (((array) $config !== $config && !$config instanceof ArrayAccess)
                || (!isset($config[$dimension]) && $this->configClass instanceof RequiresMandatoryOptionsContract)
                || (!isset($config[$dimension]) && !$this->configClass instanceof ProvidesDefaultOptionsContract)
            ) {
                return false;
            }

            if ($this->configClass instanceof ProvidesDefaultOptionsContract && !isset($config[$dimension])) {
                return true;
            }

            $config = $config[$dimension];
        }

        return (array) $config === $config || $config instanceof ArrayAccess;
    }

    /**
     * Checks if options can be retrieved from config and if not, default options (ProvidesDefaultOptions interface) or
     * an empty array will be returned.
     *
     * @param iterable    $config
     * @param string|null $configId Config name, must be provided if factory uses RequiresConfigId interface
     *
     * @return iterable             options Default options or an empty array
     */
    public function optionsWithFallback(iterable $config, ?string $configId = null)
    {
        $options = [];

        if ($this->canRetrieveOptions($config, $configId)) {
            $options = $this->options($config, $configId);
        }

        if (empty($options) && $this instanceof ProvidesDefaultOptions) {
            $options = $this->configClass->getDefaultOptions();
        }

        return $options;
    }

    /**
     * Checks if a mandatory param is missing, supports recursion
     *
     * @param iterable $mandatoryOptions
     * @param iterable $config
     *
     * @throws MandatoryOptionNotFoundException
     */
    protected function checkMandatoryOptions(iterable $mandatoryOptions, iterable $config): void
    {
        foreach ($mandatoryOptions as $key => $mandatoryOption) {
            $useRecursion = !is_scalar($mandatoryOption);

            if (!$useRecursion && isset($config[$mandatoryOption])) {
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
     * Create configuration.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @throws \RuntimeException
     *
     * @return iterable
     */
    protected function configureOptions(ContainerInterface $container): iterable
    {
        if ($container->has(RepositoryContract::class)) {
            return $container->get(RepositoryContract::class);
        } elseif ($container->has('config')) {
            return $container->get('config');
        } elseif ($container->has('options')) {
            return $container->get('options');
        }

        throw new RuntimeException('No configuration found.');
    }
}
