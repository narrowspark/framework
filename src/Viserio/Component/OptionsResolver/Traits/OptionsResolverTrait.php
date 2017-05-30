<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use ArrayAccess;
use InvalidArgumentException;
use Iterator;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

trait OptionsResolverTrait
{
    /**
     * All of the configuration items.
     *
     * @var iterable
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function resolve(string $configId = null): array
    {
        $configClass = $this->getConfigClass();
        $config      = $this->options;
        $dimensions  = [];

        if ($configClass instanceof RequiresComponentConfigContract) {
            $dimensions  = $configClass->getDimensions();
            $dimensions  = $dimensions instanceof Iterator ? iterator_to_array($dimensions) : $dimensions;
        }

        if ($configClass instanceof RequiresConfigIdContract ||
            $configClass instanceof RequiresComponentConfigIdContract
        ) {
            $dimensions[] = $configId;
        } elseif ($configId !== null) {
            throw new InvalidArgumentException(
                sprintf('The factory [%s] does not support multiple instances.', get_class($this->configClass))
            );
        }

        // get configuration for provided dimensions
        $config = $this->getConfigurationDimensions($dimensions, $config, $configClass, $configId);

        if ((array) $config !== $config &&
            ! $config instanceof ArrayAccess &&
            $configClass instanceof RequiresComponentConfigIdContract
        ) {
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

        return (array) $config;
    }

    /**
     * Configurable class.
     *
     * @return \Viserio\Component\Contracts\OptionsResolver\RequiresConfig
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this;
    }

    /**
     * Checks if a mandatory param is missing, supports recursion.
     *
     * @param iterable $mandatoryOptions
     * @param iterable $config
     *
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException
     *
     * @return void
     */
    private function checkMandatoryOptions(iterable $mandatoryOptions, iterable $config): void
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

            $configClass = $this->getConfigClass();

            throw new MandatoryOptionNotFoundException(
                $configClass instanceof RequiresComponentConfigContract ? $configClass->getDimensions() : [],
                $useRecursion ? $key : $mandatoryOption
            );
        }
    }

    /**
     * Get configuration for provided dimensions.
     *
     * @param iterable                                                    $dimensions
     * @param iterable                                                    $config
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $configClass
     * @param null|string                                                 $configId
     *
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException
     *
     * @return iterable|object
     */
    private function getConfigurationDimensions(
        iterable $dimensions,
        $config,
        RequiresConfigContract $configClass,
        ?string $configId
    ) {
        foreach ($dimensions as $dimension) {
            if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
                throw new UnexpectedValueException($dimensions, $dimension);
            }

            if (! isset($config[$dimension])) {
                if (! $configClass instanceof RequiresMandatoryOptionsContract &&
                    $configClass instanceof ProvidesDefaultOptionsContract
                ) {
                    break;
                }

                throw new OptionNotFoundException($configClass, $dimension, $configId);
            }

            $config = $config[$dimension];
        }

        return $config;
    }
}
