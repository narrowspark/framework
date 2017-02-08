<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use RuntimeException;
use ArrayAccess;
use Iterator;
use InvalidArgumentException;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException;

class ComponentOptionsResolver extends AbstractOptionsResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve(?iterable $config = null, string $configId = null): iterable
    {
        $configClass = $this->getConfigurableClass();
        $config      = $this->resolveOptions($config);
        $dimensions  = $configClass->getDimensions();
        $dimensions  = $dimensions instanceof Iterator ? iterator_to_array($dimensions) : $dimensions;

        if ($configClass instanceof RequiresConfigIdContract) {
            $dimensions[] = $configId;
        } elseif ($configId !== null) {
            throw new InvalidArgumentException(
                sprintf('The factory "%s" does not support multiple instances.', __CLASS__)
            );
        }

        // get configuration for provided dimensions
        foreach ($dimensions as $dimension) {
            if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
                throw new UnexpectedValueException($dimensions, $dimension);
            }

            if (! isset($config[$dimension])) {
                if (! $configClass instanceof RequiresMandatoryOptionsContract && $configClass instanceof ProvidesDefaultOptionsContract) {
                    break;
                }

                throw OptionNotFoundException::missingOptions($this, $dimension, $configId);
            }

            $config = $config[$dimension];
        }

        if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
            throw new UnexpectedValueException($configClass->getDimensions());
        }

        if ($configClass instanceof RequiresMandatoryOptionsContract) {
            $this->checkMandatoryOptions($configClass->getMandatoryOptions(), $config);
        }

        if ($configClass instanceof ProvidesDefaultOptionsContract) {
            $options = $configClass->getDefaultOptions();
            $config  = array_replace_recursive(
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

    /**
     * {@inheritdoc}
     */
    protected function getConfigurableClass()
    {

    }

    /**
     * {@inheritdoc}
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
        }

        throw new RuntimeException('No configuration found.');
    }
}
