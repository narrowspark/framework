<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use ArrayAccess;
use Iterator;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;

class OptionsResolver extends AbstractOptionsResolver
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function resolve(?iterable $config = null, string $configId = null): iterable
    {
        $config     = $this->resolveOptions($config);
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
            if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
                throw UnexpectedValueException::invalidOptions($dimensions, $dimension);
            }

            if (! isset($config[$dimension])) {
                if (! $this->configClass instanceof RequiresMandatoryOptions && $this->configClass instanceof ProvidesDefaultOptions) {
                    break;
                }

                throw OptionNotFoundException::missingOptions($this, $dimension, $configId);
            }

            $config = $config[$dimension];
        }

        if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
            throw UnexpectedValueException::invalidOptions($this->configClass->getDimensions());
        }

        if ($this->configClass instanceof RequiresMandatoryOptions) {
            $this->checkgetMandatoryOptions($this->configClass->getMandatoryOptions(), $config);
        }

        if ($this->configClass instanceof ProvidesDefaultOptions) {
            $options = $this->configClass->getDefaultOptions();
            $config  = array_replace_recursive(
                $options instanceof Iterator ? iterator_to_array($options) : (array) $options,
                (array) $config
            );
        }

        return $config;
    }

    /**
     * Checks if a mandatory param is missing, supports recursion.
     *
     * @param iterable $getMandatoryOptions
     * @param iterable $config
     *
     * @throws MandatoryOptionNotFoundException
     */
    protected function checkgetMandatoryOptions(iterable $getMandatoryOptions, iterable $config): void
    {
        foreach ($getMandatoryOptions as $key => $mandatoryOption) {
            $useRecursion = ! is_scalar($mandatoryOption);

            if (! $useRecursion && isset($config[$mandatoryOption])) {
                continue;
            }

            if ($useRecursion && isset($config[$key])) {
                $this->checkgetMandatoryOptions($mandatoryOption, $config[$key]);

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
     * @param mixed                                 $data
     *
     * @throws \RuntimeException
     *
     * @return array|\Array
     */
    protected function resolveOptions($data)
    {
        if ($this->container !== null && $data === null) {
            if ($this->container->has(RepositoryContract::class)) {
                return $this->container->get(RepositoryContract::class);
            } elseif ($this->container->has('config')) {
                return $this->container->get('config');
            } elseif ($this->container->has('options')) {
                return $this->container->get('options');
            }
        } elseif ($data !== null) {
            return $data;
        }

        throw new RuntimeException('No configuration found.');
    }
}
